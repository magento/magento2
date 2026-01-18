<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Eav/_files/empty_attribute_set.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var GetAttributeSetByName $getAttributeSetByName */
$getAttributeSetByName = $objectManager->get(GetAttributeSetByName::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
try {
    $attributeSet = $getAttributeSetByName->execute('empty_attribute_set');
    $product = $productRepository->get('simple', true, null, true);
    $product->setAttributeSetId($attributeSet->getId());
    $productRepository->save($product);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
}
