<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Observer;

use Magento\Framework\Event\ObserverInterface;

class InitOptionRendererObserver implements ObserverInterface
{
    /**
     * Initialize product options renderer with bundle specific params
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('bundle', 'Magento\Bundle\Helper\Catalog\Product\Configuration');
        return $this;
    }
}
