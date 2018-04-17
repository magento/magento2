<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
/** @var CartItemInterfaceFactory $cartItemFactory */
$cartItemFactory = Bootstrap::getObjectManager()->get(CartItemInterfaceFactory::class);
/** @var CartManagementInterface $cartManagement */
$cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);

$itemsToBuy = [
    'VIRT-1' => 5,
    'VIRT-2' => 6,
];

$searchCriteria = $searchCriteriaBuilder
    ->addFilter('reserved_order_id', 'test_order_virt_1')
    ->create();
$cart = current($cartRepository->getList($searchCriteria)->getItems());

foreach ($itemsToBuy as $sku => $qty) {
    $product = $productRepository->get($sku);
    $cartItem = $cartItemFactory->create(
        [
            'data' => [
                CartItemInterface::KEY_SKU => $product->getSku(),
                CartItemInterface::KEY_QTY => $qty,
                CartItemInterface::KEY_QUOTE_ID => (int)$cart->getId(),
                'product_id' => (int)$product->getId(),
                'product' => $product,
            ]
        ]
    );
    $cart->addItem($cartItem);
}

$cartRepository->save($cart);
$cartManagement->placeOrder($cart->getId());
