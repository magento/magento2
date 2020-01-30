<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/multiple_products.php';

$objectManager = Bootstrap::getObjectManager();
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$defaultWebsiteId = $websiteRepository->get('base')->getId();
/** @var ProductExtensionFactory $extensionAttributesFactory */
$extensionAttributesFactory = $objectManager->get(ProductExtensionFactory::class);
/** @var OptionInterfaceFactory $optionFactory */
$optionFactory = $objectManager->get(OptionInterfaceFactory::class);
/** @var LinkInterfaceFactory $linkFactory */
$linkFactory = $objectManager->get(LinkInterfaceFactory::class);
/** @var ProductResource $productResource */
$productResource = $objectManager->get(ProductResource::class);

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_BUNDLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Bundle Product')
    ->setSku('bundle_product')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]
    )
    ->setSkuType(0)
    ->setPriceView(0)
    ->setPriceType(Price::PRICE_TYPE_DYNAMIC)
    ->setWeightType(0)
    ->setShipmentType(AbstractType::SHIPMENT_TOGETHER)
    ->setBundleOptionsData(
        [
            [
                'title' => 'Option 1',
                'default_title' => 'Option 1',
                'type' => 'multi',
                'required' => 1,
            ],
        ]
    )->setBundleSelectionsData(
        [
            [
                [
                    'product_id' => 10,
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                ],
                [
                    'product_id' => 11,
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                ],
                [
                    'product_id' => 12,
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                ]
            ]

        ]
    );

$options = [];
foreach ($product->getBundleOptionsData() as $key => $optionData) {
    $option = $optionFactory->create(['data' => $optionData]);
    $option->setSku($product->getSku());
    $option->setOptionId(null);
    $links = [];
    foreach ($product->getBundleSelectionsData()[$key] as $linkData) {
        $link = $linkFactory->create(['data' => $linkData]);
        $linkProduct = $productResource->getProductsSku([$linkData['product_id']]);
        $link->setSku(array_column($linkProduct, 'sku')[0]);
        $links[] = $link;
    }
    $option->setProductLinks($links);
    $options[] = $option;
}
$extensionAttributes = $product->getExtensionAttributes() ?: $extensionAttributesFactory->create();
$extensionAttributes->setBundleProductOptions($options);
$product->setExtensionAttributes($extensionAttributes);
$productRepository->save($product);
