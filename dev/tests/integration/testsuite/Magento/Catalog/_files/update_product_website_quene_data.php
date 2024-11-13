<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_two_stores.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/second_product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
/** @var IdentityGeneratorInterface $identityService */
$identityService = $objectManager->get(IdentityGeneratorInterface::class);
/** @var SerializerInterface $jsonEncoder */
$jsonEncoder = $objectManager->get(SerializerInterface::class);
/** @var OperationInterfaceFactory $optionFactory */
$optionFactory = $objectManager->get(OperationInterfaceFactory::class);
/** @var BulkManagementInterface $bulkManagement */
$bulkManagement = $objectManager->get(BulkManagementInterface::class);
$productIds = [(int)$productRepository->get('simple2')->getId()];
$websiteId = (int)$websiteRepository->get('test')->getId();
$bulkDescription = __('Update attributes for ' . 1 . ' selected products');
$dataToEncode = [
    'meta_information' => 'Update website assign',
    'product_ids' => $productIds,
    'store_id' => 0,
    'website_id' => $websiteId,
    'attributes' => [
        'website_assign' => [$websiteId],
        'website_detach' => [],
    ],
];
$bulkUid = $identityService->generateId();
$data = [
    'data' => [
        'bulk_uuid' => $bulkUid,
        'topic_name' => 'product_action_attribute.website.update',
        'serialized_data' => $jsonEncoder->serialize($dataToEncode),
        'status' => OperationInterface::STATUS_TYPE_OPEN,
    ],
];

$bulkManagement->scheduleBulk(
    $bulkUid,
    [$optionFactory->create($data)],
    $bulkDescription,
    1
);
