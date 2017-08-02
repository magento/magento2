<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Downloadable\Observer\InitOptionRendererObserver
 *
 * @since 2.0.0
 */
class InitOptionRendererObserver implements ObserverInterface
{
    /**
     * Initialize product options renderer with downloadable specific params
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('downloadable', \Magento\Downloadable\Helper\Catalog\Product\Configuration::class);

        return $this;
    }
}
