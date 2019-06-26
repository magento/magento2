<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var OptionFactory  $productOptionFactory */
$productOptionFactory = Bootstrap::getObjectManager()->get(OptionFactory::class);
/** @var DataObjectFactory $dataObjectFactory */
$dataObjectFactory = Bootstrap::getObjectManager()->get(DataObjectFactory::class);

/** @var ProductOption $productOption */
$productOption = $productOptionFactory->create();
$product = $productRepository->get('simple_product');
$productOptions = $productOption->getProductOptionCollection($product);
$cartItemCustomOptions = [];

/** @var ProductOption  $productOption */
foreach ($productOptions as $productOption) {
    $cartItemCustomOptions[$productOption->getId()] = 'initial value';
}

$request = $dataObjectFactory->create(
    [
        'data' => [
            'qty' => 1.0,
            'options' => $cartItemCustomOptions,
        ],
    ]
);

$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_quote', 'reserved_order_id');
$quote->addProduct($product, $request);
$cartRepository->save($quote);
