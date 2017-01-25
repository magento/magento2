<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

use Magento\Framework\App\State;

/**
 * Contains list of elements paths which should be hidden or disabled on Configuration page in Production mode.
 */
class ConcealInProductionConfigList implements ElementVisibilityInterface
{
    /**
     * The list of paths of form elements in the structure.
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
     * @param array $configs The list of paths of form elements in the structure
     */
    public function __construct(State $state, array $configs = [])
    {
        $this->state = $state;
        $this->configs = $configs;
    }

    /**
     * @inheritdoc
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
     * @param string $path The path will be normalized
     * @return string The normalized path
     */
    private function normalizePath($path)
    {
        return trim($path ,'/');
    }
}
