<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
/** @var CartManagementInterface $cartManagement */
$cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);

$searchCriteria = $searchCriteriaBuilder
    ->addFilter('reserved_order_id', 'created_order_for_test')
    ->create();
$cart = current($cartRepository->getList($searchCriteria)->getItems());

$product = $productRepository->get('configurable');
$configurableOptions = $product->getTypeInstance()->getConfigurableOptions($product);
$option = current(current($configurableOptions));
$requestData = [
    'product' => $option['product_id'],
    'super_attribute' => [key($configurableOptions) => $option['value_index']],
    'qty' => 3
];
$request = new \Magento\Framework\DataObject($requestData);
$cart->addProduct($product, $request);

$cartRepository->save($cart);
$cartManagement->placeOrder($cart->getId());
