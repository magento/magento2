<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Review\Model\ResourceModel\Review as ReviewResource;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ReviewResource $reviewResource */
$reviewResource = $objectManager->get(ReviewResource::class);
/** @var CollectionFactory $collectionFactory */
$collectionFactory = $objectManager->get(CollectionFactory::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$review = $collectionFactory->create()->addCustomerFilter(1)->getFirstItem();
if ($review->getReviewId()) {
    $reviewResource->delete($review);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/../../../Magento/Customer/_files/customer_rollback.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_rollback.php';
