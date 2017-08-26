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
 */
class ChangeTemplateObserver implements ObserverInterface
{
    /**
     * @param mixed $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getBlock()->setTemplate('Magento_ProductVideo::helper/gallery.phtml');
    }
}
