<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/*
 * Since the bundle product creation GUI doesn't allow to choose values for bundled products' custom options,
 * bundled items should not contain products with required custom options.
 * However, if to create such a bundle product, it will be always out of stock.
 */

use Magento\Catalog\Model\Product\Type;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

require __DIR__ . '/../../../../../../dev/tests/integration/testsuite/Magento/Catalog/_files/products.php';
require __DIR__ . '/product_simple_out_of_stock.php';

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$sampleProduct = $productRepository->get('simple');
/** @var $productBundleInStock \Magento\Catalog\Model\Product */
$productBundleInStock = $objectManager->create(\Magento\Catalog\Model\Product::class);
$productBundleInStock->setTypeId(Type::TYPE_BUNDLE)
    ->setId(3)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Bundle Product In Stock')
    ->setSku('bundle-product-in-stock')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(1)
    ->setPriceType(1)
    ->setShipmentType(1)
    ->setPrice(10.0)
    ->setBundleOptionsData(
        [
            [
                'title' => 'Bundle Product Items',
                'default_title' => 'Bundle Product Items',
                'type' => 'select', 'required' => 1,
                'delete' => '',
            ],
        ]
    )
    ->setBundleSelectionsData(
        [
            [
                [
                    'product_id' => $sampleProduct->getId(),
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                ],
            ],
        ]
    );
if ($productBundleInStock->getBundleOptionsData()) {
    $options = [];
    foreach ($productBundleInStock->getBundleOptionsData() as $key => $optionData) {
        if (!(bool)$optionData['delete']) {
            $option = $objectManager->create(\Magento\Bundle\Api\Data\OptionInterfaceFactory::class)
                ->create(['data' => $optionData]);
            $option->setSku($productBundleInStock->getSku());
            $option->setOptionId(null);
            $links = [];
            $bundleLinks = $productBundleInStock->getBundleSelectionsData();
            if (!empty($bundleLinks[$key])) {
                foreach ($bundleLinks[$key] as $linkData) {
                    if (!(bool)$linkData['delete']) {
                        /** @var \Magento\Bundle\Api\Data\LinkInterface$link */
                        $link = $objectManager->create(\Magento\Bundle\Api\Data\LinkInterfaceFactory::class)
                            ->create(['data' => $linkData]);
                        $linkProduct = $productRepository->getById($linkData['product_id']);
                        $link->setSku($linkProduct->getSku());
                        $link->setQty($linkData['selection_qty']);
                        if (isset($linkData['selection_can_change_qty'])) {
                            $link->setCanChangeQuantity($linkData['selection_can_change_qty']);
                        }
                        $links[] = $link;
                    }
                }
                $option->setProductLinks($links);
                $options[] = $option;
            }
        }
    }
    $extension = $productBundleInStock->getExtensionAttributes();
    $extension->setBundleProductOptions($options);
    $productBundleInStock->setExtensionAttributes($extension);
}
$productBundleInStock->save();

$sampleProductOutOfStock = $productRepository->get('simple-out-of-stock');
/** @var $productBundleOutOfStock \Magento\Catalog\Model\Product */
$productBundleOutOfStock = $objectManager->create(\Magento\Catalog\Model\Product::class);
$productBundleOutOfStock->setTypeId(Type::TYPE_BUNDLE)
    ->setId(4)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Bundle Product Out Of Stock')
    ->setSku('bundle-product-out-of-stock')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'use_config_backorders' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1
        ])
    ->setPriceView(1)
    ->setPriceType(1)
    ->setShipmentType(1)
    ->setPrice(10.0)
    ->setBundleOptionsData(
        [
            [
                'title' => 'Bundle Product Items',
                'default_title' => 'Bundle Product Items',
                'type' => 'select', 'required' => 1,
                'delete' => '',
            ],
        ]
    )
    ->setBundleSelectionsData(
        [
            [
                [
                    'product_id' => $sampleProductOutOfStock->getId(),
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                ],
            ],
        ]
    );
if ($productBundleOutOfStock->getBundleOptionsData()) {
    $options = [];
    foreach ($productBundleOutOfStock->getBundleOptionsData() as $key => $optionData) {
        if (!(bool)$optionData['delete']) {
            $option = $objectManager->create(\Magento\Bundle\Api\Data\OptionInterfaceFactory::class)
                ->create(['data' => $optionData]);
            $option->setSku($productBundleOutOfStock->getSku());
            $option->setOptionId(null);
            $links = [];
            $bundleLinks = $productBundleOutOfStock->getBundleSelectionsData();
            if (!empty($bundleLinks[$key])) {
                foreach ($bundleLinks[$key] as $linkData) {
                    if (!(bool)$linkData['delete']) {
                        /** @var \Magento\Bundle\Api\Data\LinkInterface$link */
                        $link = $objectManager->create(\Magento\Bundle\Api\Data\LinkInterfaceFactory::class)
                            ->create(['data' => $linkData]);
                        $linkProduct = $productRepository->getById($linkData['product_id']);
                        $link->setSku($linkProduct->getSku());
                        $link->setQty($linkData['selection_qty']);
                        if (isset($linkData['selection_can_change_qty'])) {
                            $link->setCanChangeQuantity($linkData['selection_can_change_qty']);
                        }
                        $links[] = $link;
                    }
                }
                $option->setProductLinks($links);
                $options[] = $option;
            }
        }
    }
    $extension = $productBundleOutOfStock->getExtensionAttributes();
    $extension->setBundleProductOptions($options);
    $productBundleOutOfStock->setExtensionAttributes($extension);
}
$productBundleOutOfStock->save();
