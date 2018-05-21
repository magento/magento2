<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\ElementVisibility;

use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Config\ConfigOptionsListConstants as Constants;

/**
 * Defines status of visibility of form elements on Stores > Settings > Configuration page
 * in Admin Panel in Production mode.
 * @api
 */
class ConcealScdField implements ElementVisibilityInterface
{
    /**
     * The list of form element paths with concrete visibility status.
     *
     * E.g.
     *
     * ```php
     * [
     *      'general/locale/code' => ElementVisibilityInterface::DISABLED,
     *      'general/country' => ElementVisibilityInterface::HIDDEN,
     * ];
     * ```
     *
     * It means that:
     *  - field Locale (in group Locale Options in section General) will be disabled
     *  - group Country Options (in section General) will be hidden
     *
     * @var array
     */
    private $configs = [];

    /**
     * The object that has information about the state of the system.
     *
     * @var State
     */
    private $state;

    /**
     *
     * The list of form element paths which ignore visibility status.
     *
     * E.g.
     *
     * ```php
     * [
     *      'general/country/default' => '',
     * ];
     * ```
     *
     * It means that:
     *  - field 'default' in group Country Options (in section General) will be showed, even if all group(section)
     *    will be hidden.
     *
     * @var array
     */
    private $exemptions = [];

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param State $state The object that has information about the state of the system
     * @param DeploymentConfig $deploymentConfig Deployment configuration reader
     * @param array $configs The list of form element paths with concrete visibility status.
     * @param array $exemptions The list of form element paths which ignore visibility status.
     */
    public function __construct(
        State $state,
        DeploymentConfig $deploymentConfig,
        array $configs = [],
        array $exemptions = []
    ) {
        $this->state = $state;
        $this->deploymentConfig = $deploymentConfig;
        $this->configs = $configs;
        $this->exemptions = $exemptions;
    }

    /**
     * @inheritdoc
     */
    public function isHidden($path)
    {
        $path = $this->normalizePath($path);
        if ($this->state->getMode() === State::MODE_PRODUCTION
            && !$this->deploymentConfig->getConfigData(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            && preg_match('/(?<group>(?<section>.*?)\/.*?)\/.*?/', $path, $match)) {
            $group = $match['group'];
            $section = $match['section'];
            $exemptions = array_keys($this->exemptions);
            $checkedItems = [];
            foreach ([$path, $group, $section] as $itemPath) {
                $checkedItems[] = $itemPath;
                if (!empty($this->configs[$itemPath])) {
                    return $this->configs[$itemPath] === static::HIDDEN
                        && empty(array_intersect($checkedItems, $exemptions));
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function isDisabled($path)
    {
        $path = $this->normalizePath($path);
        if ($this->state->getMode() === State::MODE_PRODUCTION
            && !$this->deploymentConfig->getConfigData(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)) {
            while ($path) {
                if (!empty($this->configs[$path])) {
                    return $this->configs[$path] === static::DISABLED;
                }

                $position = strripos($path, '/');
                if ($position === false) {
                    break;
                }
                $path = $position !== false ? substr($path, 0, $position) : null;
            }
        }

        return false;
    }

    /**
     * Returns normalized path.
     *
     * @param string $path The path to be normalized
     * @return string The normalized path
     */
    private function normalizePath($path)
    {
        return trim($path, '/');
    }
}
