<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/CatalogSearch/_files/searchable_attribute.php');

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var ProductAttributeRepositoryInterface $productAttributeRepository */
$productAttributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
$attribute = $productAttributeRepository->get('test_searchable_attribute');

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
$product = $productFactory->create();
$product->isObjectNew(true);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Simple product name')
    ->setSku('24-mb01')
    ->setPrice(100)
    ->setWeight(1)
    ->setShortDescription('Product short description')
    ->setTaxClassId(0)
    ->setDescription('Product description')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setTestSearchableAttribute($attribute->getSource()->getOptionId('Option 1'))
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]
    );
$productRepository->save($product);
