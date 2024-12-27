<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class AddProductToCart implements DataFixtureInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var DataObjectFactory
     */
    private DataObjectFactory $dataObjectFactory;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory,
    ) {
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'cart_id'    => (int) Cart ID. Required.
     *      'product_id' => (int) Product ID. Required.
     *      'qty'        => (int) Quantity. Optional. Default: 1.
     *      'buy_request'=> (array|DataObject) advanced product configuration
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $cart = $this->cartRepository->get($data['cart_id']);
        $product = $this->productRepository->getById($data['product_id']);
        $qty = $data['qty'] ?? 1;
        if (isset($data['buy_request'])) {
            $buyRequest = $data['buy_request'] instanceof DataObject
                ? $data['buy_request']
                : $this->dataObjectFactory->create(['data' => $data['buy_request']]);
        }
        $catItem = $cart->addProduct($product, $buyRequest ?? $qty);
        $this->cartRepository->save($cart);
        if (is_string($catItem)) {
            throw new LocalizedException(__($catItem));
        }
        return $catItem;
    }
}
