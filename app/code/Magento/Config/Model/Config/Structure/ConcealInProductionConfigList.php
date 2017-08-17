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
     * @param State $state The object that has information about the state of the system
     * @param array $configs The list of form element paths with concrete visibility status.
     * @since 100.2.0
     */
    public function __construct(State $state, array $configs = [])
    {
        $this->state = $state;
        $this->configs = $configs;
    }

    /**
     * @inheritdoc
     * @since 100.2.0
     */
    public function isHidden($path)
    {
        $path = $this->normalizePath($path);
        return $this->state->getMode() === State::MODE_PRODUCTION
            && !empty($this->configs[$path])
            && $this->configs[$path] === static::HIDDEN;
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
