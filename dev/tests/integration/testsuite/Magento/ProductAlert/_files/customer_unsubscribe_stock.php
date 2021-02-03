<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\ProductAlert\Model\ResourceModel\Stock;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

Bootstrap::getInstance()->reinitialize();

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);

/** @var $product Product */
$product = $objectManager->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setId(30221)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Simple Product Out Of Stock')
    ->setSku('simple-out-of-stock')
    ->setPrice(10)
    ->setWeight(1)
    ->setShortDescription("Short description")
    ->setTaxClassId(0)
    ->setDescription('Description with <b>html tag</b>')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 0,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 0,
        ]
    )
    ->setCanSaveCustomOptions(true)
    ->setHasOptions(true);

/** @var ProductRepositoryInterface $productRepositoryFactory */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->save($product);

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2]
);

$resource = $objectManager->get(Stock::class);

/** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
$dateTime = $objectManager->get(DateTimeFactory::class)->create();
$date = $dateTime->gmtDate(null, ($dateTime->gmtTimestamp() - 3600));
$productId = $productRepository->get('simple-out-of-stock')->getId();

$resource->getConnection()->insert(
    $resource->getMainTable(),
    [
        'customer_id' => 1,
        'product_id' => $productId,
        'website_id' => 1,
        'store_id' => 1,
        'add_date' => $date,
        'send_date' => null,
        'send_count' => 0,
        'status' => 0
    ]
);
