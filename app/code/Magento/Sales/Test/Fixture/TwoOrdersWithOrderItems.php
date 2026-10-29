<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Creates two sequential guest orders with order items for async grid indexer tests.
 *
 * Apply together with {@see \Magento\Catalog\Test\Fixture\Product} so the product SKU exists.
 *
 * Usage:
 * <pre>
 *  #[
 *      DataFixture(Product::class, ['sku' => 'simple', 'name' => 'Simple Product'], as: 'product'),
 *      DataFixture(TwoOrdersWithOrderItems::class, ['product_sku' => '$product.sku$'], as: 'twoOrders'),
 *  ]
 * </pre>
 */
class TwoOrdersWithOrderItems implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'product_sku' => 'simple',
        'first_increment_id' => '100000001',
        'second_increment_id' => '100000002',
        'qty' => 2,
        'price' => 100.00,
        'customer_email' => 'customer@null.com',
    ];

    /**
     * @param PlaceOrderWithCustomerOrGuest $placeOrderFixture
     */
    public function __construct(
        private readonly PlaceOrderWithCustomerOrGuest $placeOrderFixture
    ) {
    }

    /**
     * @param array $data
     * @return DataObject
     */
    public function apply(array $data = []): DataObject
    {
        $data = array_replace(self::DEFAULT_DATA, $data);
        $item = [
            'sku' => $data['product_sku'],
            'qty' => (float)$data['qty'],
            'price' => (float)$data['price'],
            'base_price' => (float)$data['price'],
        ];
        $orderData = [
            'customer_is_guest' => true,
            'customer_email' => $data['customer_email'],
            'state' => Order::STATE_PROCESSING,
            'subtotal' => $data['price'],
            'base_subtotal' => $data['price'],
            'grand_total' => $data['price'],
            'base_grand_total' => $data['price'],
            'items' => [$item],
        ];

        $firstOrder = $this->placeOrderFixture->apply(
            array_merge($orderData, ['increment_id' => $data['first_increment_id']])
        );
        $secondOrder = $this->placeOrderFixture->apply(
            array_merge($orderData, ['increment_id' => $data['second_increment_id']])
        );

        return new DataObject(
            [
                'first_order' => $firstOrder,
                'second_order' => $secondOrder,
            ]
        );
    }

    /**
     * @param DataObject $data
     * @return void
     */
    public function revert(DataObject $data): void
    {
        $firstOrder = $data->getData('first_order');
        $secondOrder = $data->getData('second_order');
        if ($secondOrder instanceof DataObject) {
            $this->placeOrderFixture->revert($secondOrder);
        }
        if ($firstOrder instanceof DataObject) {
            $this->placeOrderFixture->revert($firstOrder);
        }
    }
}
