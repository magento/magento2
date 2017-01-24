<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

use Magento\Framework\App\State;

/**
 * Contains list of elements paths which should be hidden or disabled on Configuration form in Production mode.
 */
class ProductionVisibility implements ElementVisibilityInterface
{
    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var State
     */
    private $state;

    /**
     * @param State $state
     * @param array $configs
     */
    public function __construct(State $state, array $configs = [])
    {
        $this->state = $state;
        $this->configs = $configs;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden($path)
    {
        return $this->state->getMode() === State::MODE_PRODUCTION
            && !empty($this->configs[$path])
            && $this->configs[$path] === static::HIDDEN;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled($path)
    {
        return $this->state->getMode() === State::MODE_PRODUCTION
            && !empty($this->configs[$path])
            && $this->configs[$path] === static::DISABLED;
    }
}
