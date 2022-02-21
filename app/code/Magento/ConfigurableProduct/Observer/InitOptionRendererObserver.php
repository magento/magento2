<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Observer;

use Magento\ConfigurableProduct\Helper\Catalog\Product\Configuration;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Initiates render options
 */
class InitOptionRendererObserver implements ObserverInterface
{
    /**
     * Initialize product options renderer with configurable specific params
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('configurable', Configuration::class);
        return $this;
    }
}
