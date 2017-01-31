<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(
    \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
);

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')
    ->getStore()->getId();

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
    ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
    ->setStoreId($storeId)
    ->setStores([$storeId])
    ->save();

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry')->register(
    'review_data',
    $review
);

/** @var \Magento\Review\Model\ResourceModel\Review\Collection $ratingCollection */
$ratingCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    '\Magento\Review\Model\Rating'
)->getCollection()
    ->setPageSize(2)
    ->setCurPage(1);

foreach ($ratingCollection as $rating) {
    $rating->setStores([$storeId])->setIsActive(1)->save();
}

foreach ($ratingCollection as $rating) {
    $ratingOption = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        ->create('\Magento\Review\Model\Rating\Option')
        ->getCollection()
        ->setPageSize(1)
        ->setCurPage(2)
        ->addRatingFilter($rating->getId())
        ->getFirstItem();
    $rating->setReviewId($review->getId())
        ->addOptionVote($ratingOption->getId(), $product->getId());
}

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry')->register(
    'rating_data',
    $ratingCollection->getFirstItem()
);
