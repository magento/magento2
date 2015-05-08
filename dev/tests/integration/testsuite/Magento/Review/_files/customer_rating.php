<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/customer_review.php';

$storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\Store\Model\StoreManagerInterface')
    ->getStore()
    ->getId();
/** @var \Magento\Review\Model\Rating $ratingModel */
$ratingModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Review\Model\Rating',
    ['data' => [
        'rating_code' => 'test',
        'position' => 0,
        'isActive' => 1
    ]]
);
$ratingModel->setStoreId($storeId)
    ->setStores([$storeId])
    ->setEntityId($ratingModel->getEntityIdByCode(Magento\Review\Model\Rating::ENTITY_PRODUCT_CODE))
    ->setRatingCodes(['test']);
$ratingModel->save();

/** @var \Magento\Review\Model\Rating\Option $optionModel */
$optionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Review\Model\Rating\Option'
);
$optionModel->setCode(1)
    ->setValue(1)
    ->setRatingId($ratingModel->getId())
    ->setPosition(1)
    ->setReviewId($review->getId())
    ->setEntityPkValue($product->getId())
    ->save();
$optionModel->addVote();

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry')->register(
    'rating_data',
    $ratingModel
);
