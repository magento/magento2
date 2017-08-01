<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\ProductVideo\Observer\ChangeTemplateObserver
 *
 * @since 2.0.0
 */
class ChangeTemplateObserver implements ObserverInterface
{
    /**
     * @param mixed $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getBlock()->setTemplate('Magento_ProductVideo::helper/gallery.phtml');
    }
}
