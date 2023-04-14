<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Quote\Api\CartRepositoryInterface;

class AddProductToCart extends \Magento\Quote\Test\Fixture\AddProductToCart
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Grouped
     */
    private Grouped $productType;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param Grouped $productType
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory,
        Grouped $productType
    ) {
        parent::__construct($cartRepository, $productRepository, $dataObjectFactory);
        $this->productRepository = $productRepository;
        $this->productType = $productType;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * $data['child_products'] can be supplied in following formats:
     *      - ["$product1.id$", "$product2.id$"]
     *      - [{"product_id":"$product1.id$","qty":1}, {"product_id":"$product2.id$","qty":1}]
     * <pre>
     *    $data = [
     *      'cart_id'           => (int) Cart ID. Required.
     *      'product_id'        => (int) Product ID. Required.
     *      'child_products'    => (array) array of associated products configuration. Required.
     *      'qty'               => (int) Quantity. Optional. Default: 1.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $buyRequest = [
            'super_group' => [],
            'qty' => $data['qty'] ?? 1,
        ];
        $bundleProduct = $this->productRepository->getById($data['product_id']);
        $default = [];
        foreach ($this->productType->getAssociatedProducts($bundleProduct) as $childProduct) {
            $default[$childProduct->getId()] = $childProduct->getQty();
        }
        foreach ($data['child_products'] as $item) {
            if (is_array($item)) {
                $productId = (int) $item['product_id'];
                $qty = $item['qty'] ?? null;
            } else {
                $productId = (int) $item;
                $qty = null;
            }
            $qty ??= $default[$productId];
            $buyRequest['super_group'][$productId] = $qty;
        }
        $buyRequest['super_group'] = empty($data['child_products'])
            ? $default
            : array_intersect_key($buyRequest['super_group'], $default);
        return parent::apply(
            [
                'cart_id' => $data['cart_id'],
                'product_id' => $data['product_id'],
                'buy_request' => $buyRequest
            ]
        );
    }
}
