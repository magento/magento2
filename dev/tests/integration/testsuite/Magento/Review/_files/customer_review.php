<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(
    \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
);

$review = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Review\Model\Review',
    ['data' => [
        'customer_id' => $customer->getId(),
        'title' => 'Review Summary',
        'detail' => 'Review text',
        'nickname' => 'Nickname',
    ]]
);

$review
    ->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
    ->setEntityPkValue($product->getId())
    ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
    ->setStoreId(
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()->getId()
    )
    ->setStores([
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()->getId()
    ])
    ->save();

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry')->register(
    'review_data',
    $review
);
