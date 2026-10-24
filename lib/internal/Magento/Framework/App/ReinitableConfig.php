<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * @inheritdoc
 * @deprecated 101.0.0
 */
class ReinitableConfig extends MutableScopeConfig implements ReinitableConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function reinit()
    {
        $this->clean();
        return $this;
    }
}
