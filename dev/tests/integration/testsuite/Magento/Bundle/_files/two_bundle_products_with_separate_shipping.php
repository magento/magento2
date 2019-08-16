<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Model\Product\Price as BundlePrice;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as BundleProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/multiple_products.php';

$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

/** @var ProductFactory $productFactory */
$productFactory = $objectManager->create(ProductFactory::class);

/** @var OptionInterfaceFactory $bundleOptionFactory */
$bundleOptionFactory = $objectManager->create(OptionInterfaceFactory::class);

/** @var LinkInterfaceFactory $bundleLinkFactory */
$bundleLinkFactory = $objectManager->create(LinkInterfaceFactory::class);

$bundleProduct = $productFactory->create();
$attributeSetId = $bundleProduct->getDefaultAttributeSetId();
$bundleProduct->setTypeId(BundleProductType::TYPE_BUNDLE)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Bundle Product With Separate Items Shipping')
    ->setSku('bundle-product-separate-shipping-1')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(1)
    ->setPriceType(BundlePrice::PRICE_TYPE_FIXED)
    ->setPrice(10.0)
    ->setShipmentType(1)
    ->setBundleOptionsData(
        [
            [
                'title' => 'Option 1',
                'default_title' => 'Option 1',
                'type' => 'radio',
                'required' => 1,
                'delete' => '',
            ],
            [
                'title' => 'Option 2',
                'default_title' => 'Option 2',
                'type' => 'radio',
                'required' => 1,
                'delete' => '',
            ],
        ]
    )->setBundleSelectionsData(
        [
            [
                ['product_id' => 10, 'selection_qty' => 1, 'selection_can_change_qty' => 1, 'delete' => ''],
                ['product_id' => 11, 'selection_qty' => 1, 'selection_can_change_qty' => 1, 'delete' => ''],
            ],
            [
                ['product_id' => 12, 'selection_qty' => 1, 'selection_can_change_qty' => 1, 'delete' => ''],
                ['product_id' => 13, 'selection_qty' => 1, 'selection_can_change_qty' => 1, 'delete' => ''],
            ],
        ]
    );

$bundleProduct2 = $productFactory->create(['data' => $bundleProduct->getData()]);
$bundleProduct2
    ->setName('Bundle Product With Separate Items Shipping Two')
    ->setSku('bundle-product-separate-shipping-2');

foreach ([$bundleProduct, $bundleProduct2] as $product) {
    if ($product->getBundleOptionsData()) {
        $options = [];
        foreach ($product->getBundleOptionsData() as $key => $optionData) {
            if (!(bool)$optionData['delete']) {
                $option = $bundleOptionFactory->create(['data' => $optionData]);
                $option->setSku($product->getSku());
                $option->setOptionId(null);

                $links = [];
                $bundleLinks = $product->getBundleSelectionsData();
                if (!empty($bundleLinks[$key])) {
                    foreach ($bundleLinks[$key] as $linkData) {
                        if (!(bool)$linkData['delete']) {
                            $link = $bundleLinkFactory->create(['data' => $linkData]);
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
}
