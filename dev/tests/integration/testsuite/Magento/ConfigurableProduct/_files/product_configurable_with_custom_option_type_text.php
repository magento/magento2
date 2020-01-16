<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/product_configurable.php';

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var ProductCustomOptionInterfaceFactory $optionRepository */
$optionRepository = $objectManager->get(ProductCustomOptionInterfaceFactory::class);

$configurableProduct = $productRepository->get('configurable');
$createdOption = $optionRepository->create([
    'data' => [
        'is_require' => 0,
        'sku' => 'option-1',
        'title' => 'Option 1',
        'type' => ProductCustomOptionInterface::OPTION_TYPE_AREA,
        'price' => 15,
        'price_type' => 'fixed',
    ]
]);
$createdOption->setProductSku($configurableProduct->getSku());
$configurableProduct->setOptions([$createdOption]);
$productRepository->save($configurableProduct);
