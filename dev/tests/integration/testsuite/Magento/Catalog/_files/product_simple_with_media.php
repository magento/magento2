<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Customer\Model\Group;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);

$tierPrices = [];
/** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
/** @var  $tpExtensionAttributes */
$tpExtensionAttributesFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);
/** @var  $productExtensionAttributes */
$productExtensionAttributesFactory = $objectManager->get(ProductExtensionInterfaceFactory::class);

$adminWebsite = $objectManager->get(WebsiteRepositoryInterface::class)->get('admin');
$tierPriceExtensionAttributes1 = $tpExtensionAttributesFactory->create()
    ->setWebsiteId($adminWebsite->getId());
$productExtensionAttributesWebsiteIds = $productExtensionAttributesFactory->create(
    ['website_ids' => $adminWebsite->getId()]
);

$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => Group::CUST_GROUP_ALL,
            'qty' => 2,
            'value' => 8
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttributes1);

$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => Group::CUST_GROUP_ALL,
            'qty' => 5,
            'value' => 5
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttributes1);

$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => Group::NOT_LOGGED_IN_ID,
            'qty' => 3,
            'value' => 5
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttributes1);

$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => Group::NOT_LOGGED_IN_ID,
            'qty' => 3.2,
            'value' => 6,
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttributes1);

$tierPriceExtensionAttributes2 = $tpExtensionAttributesFactory->create()
    ->setWebsiteId($adminWebsite->getId())
    ->setPercentageValue(50);

$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => Group::NOT_LOGGED_IN_ID,
            'qty' => 10
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttributes2);

/** @var $product Product */
$product = $objectManager->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setId(812)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product With Media')
    ->setSku('simple_product_with_media')
    ->setPrice(10)
    ->setWeight(1)
    ->setShortDescription("Short description")
    ->setTaxClassId(2)
    ->setTierPrices($tierPrices)
    ->setDescription('Description with <b>html tag</b>')
    ->setExtensionAttributes($productExtensionAttributesWebsiteIds)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
        ]
    )->setCanSaveCustomOptions(true)
    ->setHasOptions(true);

$oldOptions = [
    [
        'previous_group' => 'text',
        'title'     => 'Test Field',
        'type'      => 'field',
        'is_require' => 1,
        'sort_order' => 0,
        'price'     => 1,
        'price_type' => 'fixed',
        'sku'       => '1-text',
        'max_characters' => 100,
    ],
    [
        'previous_group' => 'date',
        'title'     => 'Test Date and Time',
        'type'      => 'date_time',
        'is_require' => 1,
        'sort_order' => 0,
        'price'     => 2,
        'price_type' => 'fixed',
        'sku'       => '2-date',
    ],
    [
        'previous_group' => 'select',
        'title'     => 'Test Select',
        'type'      => 'drop_down',
        'is_require' => 1,
        'sort_order' => 0,
        'values'    => [
            [
                'option_type_id' => null,
                'title'         => 'Option 1',
                'price'         => 3,
                'price_type'    => 'fixed',
                'sku'           => '3-1-select',
            ],
            [
                'option_type_id' => null,
                'title'         => 'Option 2',
                'price'         => 3,
                'price_type'    => 'fixed',
                'sku'           => '3-2-select',
            ],
        ]
    ],
    [
        'previous_group' => 'select',
        'title'     => 'Test Radio',
        'type'      => 'radio',
        'is_require' => 1,
        'sort_order' => 0,
        'values'    => [
            [
                'option_type_id' => null,
                'title'         => 'Option 1',
                'price'         => 3,
                'price_type'    => 'fixed',
                'sku'           => '4-1-radio',
            ],
            [
                'option_type_id' => null,
                'title'         => 'Option 2',
                'price'         => 3,
                'price_type'    => 'fixed',
                'sku'           => '4-2-radio',
            ],
        ]
    ]
];

$options = [];

/** @var ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = $objectManager->create(ProductCustomOptionInterfaceFactory::class);

foreach ($oldOptions as $option) {
    /** @var ProductCustomOptionInterface $option */
    $option = $customOptionFactory->create(['data' => $option]);
    $option->setProductSku($product->getSku());

    $options[] = $option;
}

$product->setOptions($options);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$productRepository->save($product);

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2]
);
