<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product\Type\AbstractType;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);

$extensionAttributesFactory = $objectManager->get(ExtensionAttributesFactory::class);
$bundleOptionFactory = $objectManager->get(OptionInterfaceFactory::class);
$productLinkFactory = $objectManager->get(LinkInterfaceFactory::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$productsData = [
    [
        'attributes'        => [
            'attribute_set_id' => 4,
            'type_id'          => Type::TYPE_BUNDLE,
            'sku'              => 'SKU-BUNDLE-1',
            'name'             => 'Bundle Product Blue',
            'status'           => Status::STATUS_ENABLED,
            'stock_data'       => ['is_in_stock' => true]
        ],
        'custom_attributes' => [
            'price_type'    => Price::PRICE_TYPE_DYNAMIC,
            'shipment_type' => AbstractType::SHIPMENT_SEPARATELY,
            'sku_type'      => 0,
            'price_view'    => 1
        ],
        'simple_links'      => [
            [
                'sku'   => 'SKU-1',
                'qty'   => 2,
                'title' => 'Simple Product Orange'
            ],
            [
                'sku'   => 'SKU-3',
                'qty'   => 3,
                'title' => 'Simple Product Blue'
            ]
        ]
    ],
    [
        'attributes'        => [
            'attribute_set_id' => 4,
            'type_id'          => Type::TYPE_BUNDLE,
            'sku'              => 'SKU-BUNDLE-2',
            'name'             => 'Bundle Product White',
            'status'           => Status::STATUS_ENABLED,
            'stock_data'       => ['is_in_stock' => true]
        ],
        'custom_attributes' => [
            'price_type'    => Price::PRICE_TYPE_DYNAMIC,
            'shipment_type' => AbstractType::SHIPMENT_TOGETHER,
            'sku_type'      => 0,
            'price_view'    => 1
        ],
        'simple_links'      => [
            [
                'sku'   => 'SKU-2',
                'qty'   => 3,
                'title' => 'Simple Product White'
            ],
            [
                'sku'   => 'SKU-3',
                'qty'   => 4,
                'title' => 'Simple Product Blue'
            ]
        ]
    ]
];

foreach ($productsData as $productData) {
    /** @var \Magento\Catalog\Model\Product $product */
    $product = $productFactory->create();
    foreach ($productData['attributes'] as $code => $value) {
        $product->setDataUsingMethod($code, $value);
    }
    $product->setCustomAttributes($productData['custom_attributes']);

    $options = [];
    foreach ($productData['simple_links'] as $linkData) {
        /** @var Magento\Bundle\Api\Data\LinkInterface $link */
        $link = $productLinkFactory->create();
        $link->setSku($linkData['sku']);
        $link->setQty($linkData['qty']);
        $link->setCanChangeQuantity(1);

        /** @var Magento\Bundle\Api\Data\OptionInterface $option */
        $option = $bundleOptionFactory->create();
        $option->setTitle($linkData['title']);
        $option->setRequired(true);
        $option->setType('select');
        $option->setProductLinks([$link]);
        $options[] = $option;
    }

    /** @var \Magento\Catalog\Api\Data\ProductExtensionInterface $extensionAttributes */
    $extensionAttributes = $extensionAttributesFactory->create(ProductInterface::class);
    $extensionAttributes->setBundleProductOptions($options);

    $product->setExtensionAttributes($extensionAttributes);
    $product = $productRepository->save($product);
}
