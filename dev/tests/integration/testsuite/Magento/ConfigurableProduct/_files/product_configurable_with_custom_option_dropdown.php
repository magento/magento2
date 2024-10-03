<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable.php');

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var ProductCustomOptionInterfaceFactory $optionRepository */
$optionRepository = $objectManager->get(ProductCustomOptionInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
$product = $productRepository->get('configurable');
$dropdownOption = [
    'previous_group' => 'select',
    'title' => 'Dropdown Options',
    'type' => 'drop_down',
    'is_require' => 1,
    'sort_order' => 0,
    'values' => [
        [
            'option_type_id' => null,
            'title' => 'Option 1',
            'price' => '10.00',
            'price_type' => 'fixed',
            'sku' => 'opt1',
        ],
        [
            'option_type_id' => null,
            'title' => 'Option 2',
            'price' => '20.00',
            'price_type' => 'fixed',
            'sku' => 'opt2',
        ],
    ]
];

$createdOption = $optionRepository->create(['data' => $dropdownOption]);
$createdOption->setProductSku($product->getSku());
$product->setOptions([$createdOption]);
$productRepository->save($product);
