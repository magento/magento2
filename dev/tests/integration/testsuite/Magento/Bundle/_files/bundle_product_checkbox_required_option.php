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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_duplicated.php';
require __DIR__ . '/../../../Magento/Catalog/_files/second_product_simple.php';

$objectManager = Bootstrap::getObjectManager();
/** @var ProductResource $productResource */
$productResource = $objectManager->get(ProductResource::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsiteId = $websiteRepository->get('base')->getId();
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
$product = $productFactory->create();
$product->setTypeId(Type::TYPE_BUNDLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$baseWebsiteId])
    ->setName('Bundle Product')
    ->setSku('bundle-product')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(0)
    ->setSkuType(1)
    ->setWeightType(1)
    ->setPriceType(Price::PRICE_TYPE_DYNAMIC)
    ->setPrice(10.0)
    ->setShipmentType(AbstractType::SHIPMENT_TOGETHER)
    ->setBundleOptionsData(
        [
            [
                'title' => 'Checkbox Options',
                'default_title' => 'Checkbox Options',
                'type' => 'checkbox',
                'required' => 1,
                'delete' => '',
            ],

        ]
    )->setBundleSelectionsData(
        [
            [
                [
                    'product_id' => $productResource->getIdBySku('simple-1'),
                    'selection_qty' => 1,
                    'selection_price_value' => 0,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                    'option_id' => 1
                ],
            ],
        ]
    );
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$options = [];
/** @var LinkInterfaceFactory $linkFactory */
$linkFactory = $objectManager->get(LinkInterfaceFactory::class);
/** @var OptionInterfaceFactory $optionLinkFactory */
$optionLinkFactory =  $objectManager->get(OptionInterfaceFactory::class);
foreach ($product->getBundleOptionsData() as $key => $optionData) {
    $option = $optionLinkFactory->create(['data' => $optionData]);
    $option->setSku($product->getSku());
    $option->setOptionId(null);
    $links = [];
    $bundleLinks = $product->getBundleSelectionsData();
    $productIds = $productResource->getProductsSku(array_column($bundleLinks[$key], 'product_id'));
    foreach ($bundleLinks[$key] as $linkKey => $linkData) {
        $link = $linkFactory->create(['data' => $linkData]);
        $linkProductSku = $productIds[$linkKey]['sku'];
        $link->setSku($linkProductSku);
        $link->setQty($linkData['selection_qty']);
        $link->setPrice($linkData['selection_price_value']);
        $links[] = $link;
    }
    $option->setProductLinks($links);
    $options[] = $option;
}
/** @var ProductExtensionFactory $extensionAttributesFactory */
$extensionAttributesFactory = $objectManager->get(ProductExtensionFactory::class);
$extensionAttributes = $product->getExtensionAttributes() ?? $extensionAttributesFactory->create();
$extensionAttributes->setBundleProductOptions($options);
$product->setExtensionAttributes($extensionAttributes);

$productRepository->save($product);
