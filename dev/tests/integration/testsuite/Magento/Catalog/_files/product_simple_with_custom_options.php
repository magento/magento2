<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple_with_custom_options')
    ->setPrice(10)
    ->setWeight(1)
    ->setShortDescription("Short description")
    ->setTaxClassId(0)
    ->setDescription('Description with <b>html tag</b>')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]
    )->setCanSaveCustomOptions(true)
    ->setHasOptions(true);

$oldOptions = [
    [
        'previous_group' => 'select',
        'title' => 'Test Select',
        'type' => 'drop_down',
        'is_require' => 1,
        'sort_order' => 0,
        'values' => [
            [
                'option_type_id' => null,
                'title' => 'Option 1',
                'price' => '3,000.00',
                'price_type' => 'fixed',
                'sku' => '3-1-select',
            ],
            [
                'option_type_id' => null,
                'title' => 'Option 2',
                'price' => '5,000.00',
                'price_type' => 'fixed',
                'sku' => '3-2-select',
            ],
        ]
    ],
    [
        'previous_group' => 'select',
        'title' => 'Test Radio',
        'type' => 'radio',
        'is_require' => 1,
        'sort_order' => 0,
        'values' => [
            [
                'option_type_id' => null,
                'title' => 'Option 1',
                'price' => '600.234',
                'price_type' => 'fixed',
                'sku' => '4-1-radio',
            ],
            [
                'option_type_id' => null,
                'title' => 'Option 2',
                'price' => '40,000.00',
                'price_type' => 'fixed',
                'sku' => '4-2-radio',
            ],
        ]
    ]
];

$options = [];

/** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = $objectManager->create(\Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory::class);

foreach ($oldOptions as $option) {
    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option */
    $option = $customOptionFactory->create(['data' => $option]);
    $option->setProductSku($product->getSku());

    $options[] = $option;
}

$product->setOptions($options);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
$productRepositoryFactory = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepositoryFactory->save($product);

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2]
);
