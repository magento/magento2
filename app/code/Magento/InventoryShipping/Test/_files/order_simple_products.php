<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;

$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
$cartItemFactory = Bootstrap::getObjectManager()->get(CartItemInterfaceFactory::class);
$cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);

$searchCriteria = $searchCriteriaBuilder
    ->addFilter('reserved_order_id', 'test_order_1')
    ->create();
/** @var CartInterface $cart */
$cart = current($cartRepository->getList($searchCriteria)->getItems());
$cart->setStoreId(1);

$itemsToBuy = [
    'SKU-1' => 2.3,
    'SKU-2' => 3
];

foreach ($itemsToBuy as $sku => $qty) {
    $product = $productRepository->get($sku);
    $cartItem = $cartItemFactory->create(
        [
            'data' => [
                CartItemInterface::KEY_SKU => $product->getSku(),
                CartItemInterface::KEY_QTY => $qty,
                CartItemInterface::KEY_QUOTE_ID => (int)$cart->getId(),
                'product_id' => (int)$product->getId(),
                'product' => $product
            ]
        ]
    );
    $cart->addItem($cartItem);
}

$cartRepository->save($cart);
$cartManagement->placeOrder($cart->getId());
