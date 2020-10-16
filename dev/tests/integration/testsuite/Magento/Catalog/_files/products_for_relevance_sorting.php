<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Framework/Search/_files/products.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$categoryLinkRepository = $objectManager->create(
    \Magento\Catalog\Api\CategoryLinkRepositoryInterface::class,
    [
        'productRepository' => $productRepository
    ]
);
$categoryLinkManagement = $objectManager->create(
    \Magento\Catalog\Api\CategoryLinkManagementInterface::class,
    [
        'productRepository' => $productRepository,
        'categoryLinkRepository' => $categoryLinkRepository
    ]
);
$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(
    330
)->setCreatedAt(
    '2019-08-27 11:05:07'
)->setName(
    'Colorful Category'
)->setParentId(
    2
)->setPath(
    '1/2/330'
)->setLevel(
    2
)->setAvailableSortBy(
    ['position', 'name']
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->save();

$defaultAttributeSet = $objectManager->get(Magento\Eav\Model\Config::class)
    ->getEntityType('catalog_product')
    ->getDefaultAttributeSetId();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId(1)
    ->setWebsiteIds([1])
    ->setName('Navy Blue Striped Shoes')
    ->setSku('navy-striped-shoes')
    ->setPrice(40)
    ->setWeight(8)
    ->setDescription('blue striped flip flops at <b>one</b>')
    ->setMetaTitle('navy blue colored shoes meta title')
    ->setMetaKeyword('blue, navy, striped , women, kids')
    ->setMetaDescription('blue shoes women kids meta description')
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId(1)
    ->setWebsiteIds([1])
    ->setName('light green Shoes')
    ->setSku('light-green-shoes')
    ->setPrice(40)
    ->setWeight(8)
    ->setDescription('green polka dots shoes <b>one</b>')
    ->setMetaTitle('light green shoes meta title')
    ->setMetaKeyword('light, green , women, kids')
    ->setMetaDescription('shoes women kids meta description')
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();

/** @var \Magento\Catalog\Model\Product $greyProduct */
$greyProduct = $productRepository->get('grey_shorts');
$greyProduct->setDescription('Description with Blue lines');
$productRepository->save($greyProduct);

$skus = ['green_socks', 'white_shorts','red_trousers','blue_briefs','grey_shorts',
    'navy-striped-shoes', 'light-green-shoes'];

/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);
foreach ($skus as $sku) {
    $categoryLinkManagement->assignProductToCategories(
        $sku,
        [330]
    );
}
