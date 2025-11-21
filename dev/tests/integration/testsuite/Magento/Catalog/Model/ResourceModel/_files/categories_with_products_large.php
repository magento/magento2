<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;

$om = Bootstrap::getObjectManager();

$storeManager     = $om->get(StoreManagerInterface::class);
$rootCategoryId   = (int)$storeManager->getStore()->getRootCategoryId();

$categoryFactory    = $om->get(CategoryFactory::class);
$categoryRepository = $om->get(CategoryRepositoryInterface::class);
$productFactory     = $om->get(ProductFactory::class);
$productResource    = $om->get(ProductResource::class);

$resource = $om->get(ResourceConnection::class);
$conn = $resource->getConnection();
$table = $resource->getTableName('catalog_category_product');

$identifier       = 'bulk_test_123';
$categoriesCount  = 401;

/* products */
$products = [];
$totalProducts = 5;

for ($i = 1; $i <= $totalProducts; $i++) {
    $p = $productFactory->create();
    $p->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setSku("{$identifier}_prd_{$i}")
        ->setName("Bulk Test Product {$i}")
        ->setPrice(10 + $i)
        ->setVisibility(4)
        ->setStatus(1)
        ->setStockData(['qty' => 10, 'is_in_stock' => 1]);

    $productResource->save($p);
    $products[] = $p->getId();
}

/* category generator + inline product assignment */
$createCategory = function(string $name, int $parentId, bool $isLeaf = false)
use ($categoryFactory, $categoryRepository, $products, $conn, $table) {

    $cat = $categoryFactory->create();
    $cat->setName($name)
        ->setIsActive(true)
        ->setIsAnchor(1)
        ->setParentId($parentId);

    $categoryRepository->save($cat);
    $catId = (int)$cat->getId();

    if ($isLeaf) {
        $count = random_int(1, 5);
        $sel = [];

        for ($i = 0; $i < $count; $i++) {
            $sel[] = $products[random_int(0, count($products) - 1)];
        }

        $sel = array_unique($sel);
        $rows = [];

        foreach ($sel as $pid) {
            $rows[] = [
                'category_id' => $catId,
                'product_id'  => $pid,
                'position'    => 0
            ];
        }

        if ($rows) {
            $conn->insertMultiple($table, $rows);
        }
    }

    return $catId;
};

$leafCategories = [];
$parentCategories = [];

/* level 1 */
$level1 = array_map(
    fn($i) => $createCategory("{$identifier}_l1_{$i}", $rootCategoryId),
    range(1, 4)
);
$parentCategories = array_merge($parentCategories, $level1);

$lastL1 = end($level1);

foreach (range(1, 10) as $i) {
    $leafCategories[] = $createCategory("{$identifier}_l1_leaf_{$i}", $lastL1, true);
}

/* level 2 */
$level2 = [];

foreach ($level1 as $parentId) {

    $createdThisLoop = [];

    foreach (range(1, 13) as $i) {
        $createdThisLoop[] = $createCategory("{$identifier}_l2_{$parentId}_{$i}", $parentId);
    }

    $level2 = array_merge($level2, $createdThisLoop);
    $parentCategories = array_merge($parentCategories, $createdThisLoop);

    foreach (range(1, 5) as $i) {
        $leafCategories[] = $createCategory("{$identifier}_l2_{$parentId}_leaf_{$i}", $parentId, true);
    }
}

/* level 3 leafs */
foreach ($level2 as $parentId) {
    $leafCategories[] = $createCategory("{$identifier}_l3_{$parentId}_leaf", $parentId, true);
}

/* extend up to target count */
$totalCreated = count($parentCategories) + count($leafCategories);
$missing = max(0, $categoriesCount - $totalCreated);

for ($i = 1; $i <= $missing; $i++) {
    $parentId = $parentCategories[random_int(0, count($parentCategories) - 1)];
    $leafCategories[] = $createCategory("{$identifier}_extra_{$i}", $parentId, true);
}
