<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Observer;

class InitOptionRenderer
{
    /**
     * Initialize product options renderer with bundle specific params
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function invoke(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('bundle', 'Magento\Bundle\Helper\Catalog\Product\Configuration');
        return $this;
    }
}
