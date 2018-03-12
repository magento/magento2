<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\Website;

$objectManager = Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Model\ProductRepository $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$simpleSku1 = $productRepository->get('SKU-1');
$simpleSku2 = $productRepository->get('SKU-2');
$simpleSku3 = $productRepository->get('SKU-3');

$website = Bootstrap::getObjectManager()->create(Website::class);
$website->load('eu_website', 'code');
$websiteIds = [$website->getId()];

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds($websiteIds)
    ->setName('Bundle Product With All Children In Stock')
    ->setSku('bundle-product-eu-website')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(1)
    ->setPriceType(1)
    ->setPrice(10.0)
    ->setShipmentType(0)
    ->setBundleOptionsData(
        [
            [
                'title' => 'Option 1',
                'default_title' => 'Option 1',
                'type' => 'select',
                'required' => 0,
                'delete' => '',
            ],
        ]
    )->setBundleSelectionsData(
        [
            [
                [
                    'product_id' => $simpleSku1->getId(),
                    'selection_qty' => 10,
                    'selection_can_change_qty' => 0,
                    'delete' => '',
                    'option_id' => 1
                ],
                [
                    'product_id' => $simpleSku2->getId(),
                    'selection_qty' => 15,
                    'selection_can_change_qty' => 0,
                    'delete' => '',
                    'option_id' => 1
                ],
                [
                    'product_id' => $simpleSku3->getId(),
                    'selection_qty' => 20,
                    'selection_can_change_qty' => 0,
                    'delete' => '',
                    'option_id' => 1
                ],
            ],
        ]
    );

if ($product->getBundleOptionsData()) {
    $options = [];
    foreach ($product->getBundleOptionsData() as $key => $optionData) {
        if (!(bool)$optionData['delete']) {
            $option = $objectManager->create(\Magento\Bundle\Api\Data\OptionInterfaceFactory::class)
                ->create(['data' => $optionData]);
            $option->setSku($product->getSku());
            $option->setOptionId(null);

            $links = [];
            $bundleLinks = $product->getBundleSelectionsData();
            if (!empty($bundleLinks[$key])) {
                foreach ($bundleLinks[$key] as $linkData) {
                    if (!(bool)$linkData['delete']) {
                        $link = $objectManager->create(\Magento\Bundle\Api\Data\LinkInterfaceFactory::class)
                            ->create(['data' => $linkData]);
                        $linkProduct = $productRepository->getById($linkData['product_id']);
                        $link->setSku($linkProduct->getSku());
                        $link->setQty($linkData['selection_qty']);
                        $links[] = $link;
                    }
                }
                $option->setProductLinks($links);
                $options[] = $option;
            }
        }
    }
    $extension = $product->getExtensionAttributes();
    $extension->setBundleProductOptions($options);
    $product->setExtensionAttributes($extension);
}

$productRepository->save($product, true);
