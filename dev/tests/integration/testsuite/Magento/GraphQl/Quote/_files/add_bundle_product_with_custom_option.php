<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
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
$product = $productRepository->get('bundle-product-two-dropdown-options');
$bundleOption = current($product->getExtensionAttributes()->getBundleProductOptions());
/** @var \Magento\Bundle\Api\Data\LinkInterface $linkProduct */
$linkProduct = current($bundleOption->getProductLinks());
$customOption = current($product->getOptions());

$requestInfo = new \Magento\Framework\DataObject(
    [
        'product' => $product->getId(),
        'qty' => 1,
        'bundle_option' =>[
            $bundleOption->getOptionId() => $linkProduct->getId()
        ],
        'options' => [
            $customOption->getId() => 'test text'
        ]
    ]
);

$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_quote', 'reserved_order_id');
$quote->addProduct($product, $requestInfo);
$cartRepository->save($quote);
