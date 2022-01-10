<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class AddProductToCart implements DataFixtureInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'cart_id'    => (int) Cart ID. Required.
     *      'product_id' => (int) Product ID. Required.
     *      'qty'        => (int) Quantity. Optional. Default: 1.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $cart = $this->cartRepository->get($data['cart_id']);
        $product = $this->productRepository->getById($data['product_id']);
        $catItem = $cart->addProduct($product, $data['qty'] ?? 1);
        $this->cartRepository->save($cart);
        return $catItem;
    }
}
