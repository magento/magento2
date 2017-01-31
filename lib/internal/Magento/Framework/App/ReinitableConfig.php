<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * @inheritdoc
 */
class ReinitableConfig extends MutableScopeConfig implements ReinitableConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function reinit()
    {
        $this->_scopePool->clean();
        $this->clean();
        return $this;
    }
}
