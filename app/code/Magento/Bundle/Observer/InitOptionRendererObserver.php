<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Observer;

use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Initiates render options
 */
class InitOptionRendererObserver implements ObserverInterface
{
    /**
     * Initialize product options renderer with bundle specific params
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('bundle', Configuration::class);
        return $this;
    }
}
