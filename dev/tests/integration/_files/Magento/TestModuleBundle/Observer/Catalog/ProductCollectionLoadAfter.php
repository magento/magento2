<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleBundle\Observer\Catalog;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductCollectionLoadAfter implements ObserverInterface
{

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ): void {
        $collection = $observer->getCollection();
        $item = $collection->getFirstItem();
        $extensionAttributes = $item->getExtensionAttributes();
        $extensionAttributes->setData('some_bundle_product_test', 'some_bundle_product_test_value');
        $item->setExtensionAttributes($extensionAttributes);
    }
}

