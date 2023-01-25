<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(
    \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
);
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->create(CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$storeId = $objectManager->get(
    \Magento\Store\Model\StoreManagerInterface::class
)->getStore()->getId();

$review = $objectManager->create(
    \Magento\Review\Model\Review::class,
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

$objectManager->get(\Magento\Framework\Registry::class)->register(
    'review_data',
    $review
);

/** @var \Magento\Review\Model\ResourceModel\Review\Collection $ratingCollection */
$ratingCollection = $objectManager->create(
    \Magento\Review\Model\Rating::class
)->getCollection()
    ->setPageSize(2)
    ->setCurPage(1);

foreach ($ratingCollection as $rating) {
    $rating->setStores([$storeId])->setIsActive(1)->save();
}

foreach ($ratingCollection as $rating) {
    $ratingOption = $objectManager
        ->create(\Magento\Review\Model\Rating\Option::class)
        ->getCollection()
        ->setPageSize(1)
        ->setCurPage(2)
        ->addRatingFilter($rating->getId())
        ->getFirstItem();
    $rating->setReviewId($review->getId())
        ->addOptionVote($ratingOption->getId(), $product->getId());
}

$objectManager->get(\Magento\Framework\Registry::class)->register(
    'rating_data',
    $ratingCollection->getFirstItem()
);
