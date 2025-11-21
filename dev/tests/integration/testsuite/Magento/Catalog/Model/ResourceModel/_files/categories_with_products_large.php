<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;

$om = Bootstrap::getObjectManager();

$storeManager   = $om->get(StoreManagerInterface::class);
$rootCategoryId = (int)$storeManager->getStore()->getRootCategoryId();

$categoryFactory = $om->get(CategoryFactory::class);
$categoryRepo    = $om->get(CategoryRepositoryInterface::class);
$productFactory  = $om->get(ProductFactory::class);

$identifier      = 'bulk_test_123';
$categoriesCount = 401;

$createCategory = function(string $name, int $parentId)
use ($categoryFactory, $categoryRepo) {
    $cat = $categoryFactory->create();
    $cat->setName($name)->setIsActive(true)->setIsAnchor(1)->setParentId($parentId);
    $categoryRepo->save($cat);
    return (int)$cat->getId();
};

$leafCategories = [];

/* LEVEL 1 */
$level1 = array_map(fn($i) => $createCategory("{$identifier}_L1_{$i}", $rootCategoryId), range(1, 4));
$lastL1 = end($level1);
foreach (range(1, 10) as $i) {
    $leafCategories[] = $createCategory("{$identifier}_L1_LEAF_{$i}", $lastL1);
}

/* LEVEL 2 */
$level2 = [];
foreach ($level1 as $parentId) {
    foreach (range(1, 13) as $i) {
        $level2[] = $createCategory("{$identifier}_L2_{$parentId}_{$i}", $parentId);
    }
    foreach (range(1, 5) as $i) {
        $leafCategories[] = $createCategory("{$identifier}_L2_{$parentId}_LEAF_{$i}", $parentId);
    }
}

/* LEVEL 3 */
foreach ($level2 as $parentId) {
    $leafCategories[] = $createCategory("{$identifier}_L3_{$parentId}_LEAF_1", $parentId);
}

/* EXTEND TO 401 CATEGORIES */
$totalCreated = count($level1) + count($level2) + count($leafCategories);
$missing = max(0, $categoriesCount - $totalCreated);

for ($i = 1; $i <= $missing; $i++) {
    $parentId = $level2[array_rand($level2)];
    $leafCategories[] = $createCategory("{$identifier}_L3_EXTRA_{$i}", $parentId);
}

/* CREATE PRODUCTS */
$leafCount     = count($leafCategories);
$totalProducts = max(41, (int)($leafCount * 0.5));

for ($p = 1; $p <= $totalProducts; $p++) {

    $leafIds = [$leafCategories[array_rand($leafCategories)]];
    $extra   = array_rand($leafCategories, min(random_int(3, 8), $leafCount));
    $extra   = (array)$extra;

    foreach ($extra as $key) {
        $leafIds[] = $leafCategories[$key];
    }

    $product = $productFactory->create();
    $product->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setSku("{$identifier}_prd_{$p}")
        ->setName("Bulk Test Product {$p}")
        ->setPrice(10 + $p)
        ->setVisibility(4)
        ->setStatus(1)
        ->setStockData(['qty' => 10, 'is_in_stock' => 1])
        ->setCategoryIds(array_unique($leafIds))
        ->save();
}

/* ENSURE EACH LEAF HAS AT LEAST 1 PRODUCT */
$productSkus = array_map(fn($i) => "{$identifier}_prd_{$i}", range(1, $totalProducts));

foreach ($leafCategories as $leafId) {
    $sku = $productSkus[array_rand($productSkus)];
    $product = $productFactory->create()->loadByAttribute('sku', $sku);

    if ($product) {
        $product->setCategoryIds(array_unique([
            ...((array)$product->getCategoryIds()),
            $leafId
        ]))->save();
    }
}
