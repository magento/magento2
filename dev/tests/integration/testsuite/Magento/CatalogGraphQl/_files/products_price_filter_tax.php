<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\Rule;

$objectManager = Bootstrap::getObjectManager();

/** @var WriterInterface $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
$configWriter->save('tax/calculation/price_includes_tax', '0');
$configWriter->save('tax/display/type', '2');
$configWriter->save('tax/defaults/country', 'US');
$configWriter->save('tax/defaults/region', '12');
$configWriter->save('tax/defaults/postcode', '*');
$configWriter->save('shipping/origin/country_id', 'US');
$configWriter->save('shipping/origin/region_id', '12');
$configWriter->save('shipping/origin/postcode', '90001');

$rate = $objectManager->create(Rate::class)->setData([
    'tax_country_id' => 'US',
    'tax_region_id' => '12',
    'tax_postcode' => '*',
    'code' => 'US-CA-GraphQl-Price-Filter-Rate',
    'rate' => '7.5',
])->save();

$objectManager->create(Rule::class)->setData([
    'code' => 'US GraphQl Price Filter Tax Rule',
    'priority' => 0,
    'position' => 0,
    'customer_tax_class_ids' => [3],
    'product_tax_class_ids' => [2],
    'tax_rate_ids' => [$rate->getId()],
])->save();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);

$products = [
    ['sku' => 'graphql_price_filter_a', 'name' => 'GraphQl Price Filter A', 'price' => 9.30],
    ['sku' => 'graphql_price_filter_b', 'name' => 'GraphQl Price Filter B', 'price' => 10.00],
];

foreach ($products as $productData) {
    /** @var Product $product */
    $product = $objectManager->create(Product::class);
    $product->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName($productData['name'])
        ->setSku($productData['sku'])
        ->setPrice($productData['price'])
        ->setTaxClassId(2)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1]);
    $productRepository->save($product);
    $categoryLinkManagement->assignProductToCategories($productData['sku'], [2]);
}

/** @var IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->get(IndexerRegistry::class);
$indexerRegistry->get(Price::INDEXER_ID)->reindexAll();
$indexerRegistry->get(Fulltext::INDEXER_ID)->reindexAll();
