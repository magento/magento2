<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\WebsiteRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepository $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepository::class);
$baseWebsite = $websiteRepository->get('base');
/** @var ProductInterface $productModel */
$productModel = $objectManager->get(ProductInterface::class);
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productFactory->create(
    [
        'data' => [
            'type_id' => Type::TYPE_SIMPLE,
            'attribute_set_id' => $productModel->getDefaultAttributeSetid(),
            'website_ids' => [$baseWebsite->getId()],
            'name' => 'Simple product',
            'sku' => 'simple_product',
            'price' => 50,
            'weight' => 1,
            'tax_class_id' => 0,
            'visibility' => Visibility::VISIBILITY_BOTH,
            'status' => Status::STATUS_ENABLED,
            'stock_data' => [
                'use_config_manage_stock' => 1,
                'qty' => 100,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1,
            ]
        ]
    ]
);
$product->isObjectNew(true);
$product = $productRepository->save($product);
