<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

use Magento\Framework\App\State;

/**
 * Defines status of visibility of form elements on Stores > Settings > Configuration page
 * in Admin Panel in Production mode.
 * @api
 * @since 100.2.0
 */
class ConcealInProductionConfigList implements ElementVisibilityInterface
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
     * @param State $state The object that has information about the state of the system
     * @param array $configs The list of form element paths with concrete visibility status.
     * @param array $exemptions The list of form element paths which ignore visibility status.
     */
    public function __construct(State $state, array $configs = [], array $exemptions = [])
    {
        $this->state = $state;
        $this->configs = $configs;
        $this->exemptions = $exemptions;
    }

    /**
     * @inheritdoc
     * @since 100.2.0
     */
    public function isHidden($path)
    {
        $result = false;
        $path = $this->normalizePath($path);
        if ($this->state->getMode() === State::MODE_PRODUCTION
            && preg_match('/(?<group>(?<section>.*?)\/.*?)\/.*?/', $path, $match)) {
            $group = $match['group'];
            $section = $match['section'];
            $exemptions = array_keys($this->exemptions);
            foreach ($this->configs as $configPath => $value) {
                if ($value === static::HIDDEN && strpos($path, $configPath) !==false) {
                    $result = empty(array_intersect([$section, $group, $path], $exemptions));
                }
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @since 100.2.0
     */
    public function isDisabled($path)
    {
        $path = $this->normalizePath($path);
        if ($this->state->getMode() === State::MODE_PRODUCTION) {
            while (true) {
                if (!empty($this->configs[$path])) {
                    return $this->configs[$path] === static::DISABLED;
                }

                $position = strripos($path, '/');
                if ($position === false) {
                    break;
                }
                $path = substr($path, 0, $position);
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
