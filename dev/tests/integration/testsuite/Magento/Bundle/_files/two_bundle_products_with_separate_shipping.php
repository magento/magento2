<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/multiple_products.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\ProductRepository $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
/** @var \Magento\Catalog\Model\ProductFactory $productFactory */
$productFactory = $objectManager->create(\Magento\Catalog\Model\ProductFactory::class);
/** @var \Magento\Bundle\Api\Data\OptionInterfaceFactory $bundleOptionFactory */
$bundleOptionFactory = $objectManager->create(\Magento\Bundle\Api\Data\OptionInterfaceFactory::class);
/** @var \Magento\Bundle\Api\Data\LinkInterfaceFactory $bundleLinkFactory */
$bundleLinkFactory = $objectManager->create(\Magento\Bundle\Api\Data\LinkInterfaceFactory::class);

/** @var $bundleProduct \Magento\Catalog\Model\Product */
$bundleProduct = $productFactory->create();
$bundleProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Bundle Product With Separate Items Shipping')
    ->setSku('bundle-product-separate-shipping-1')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(1)
    ->setPriceType(1)
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
