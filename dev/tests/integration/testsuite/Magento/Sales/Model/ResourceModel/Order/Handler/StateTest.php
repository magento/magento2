<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Handler;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests order state after invoicing and shipping items with decimal quantities.
 *
 * @see State
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StateTest extends TestCase
{
    /**
     * @return void
     */
    #[
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        DataFixture(
            ProductFixture::class,
            [
                'extension_attributes' => [
                    'stock_item' => ['is_qty_decimal' => true, 'use_config_min_sale_qty' => 0, 'min_sale_qty' => 0.1]
                ]
            ],
            'product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => 'guest@example.com']),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 0.5]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            SetDeliveryMethodFixture::class,
            ['cart_id' => '$cart.id$', 'carrier_code' => 'flatrate', 'method_code' => 'flatrate']
        ),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
    ]
    public function testOrderStaysProcessingAfterInvoiceOfDecimalQtyBelowOne(): void
    {
        $order = $this->getOrder();

        $this->assertSame(Order::STATE_PROCESSING, $order->getState());
        $this->assertTrue($order->canShip());
    }

    /**
     * @return void
     */
    #[
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        DataFixture(
            ProductFixture::class,
            [
                'extension_attributes' => [
                    'stock_item' => ['is_qty_decimal' => true, 'use_config_min_sale_qty' => 0, 'min_sale_qty' => 0.1]
                ]
            ],
            'product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => 'guest@example.com']),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1.5]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            SetDeliveryMethodFixture::class,
            ['cart_id' => '$cart.id$', 'carrier_code' => 'flatrate', 'method_code' => 'flatrate']
        ),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
    ]
    public function testOrderCompletesOnlyAfterWholeDecimalQtyIsShipped(): void
    {
        $order = $this->getOrder();
        $itemId = (int)current($order->getItems())->getItemId();
        $shipOrder = Bootstrap::getObjectManager()->get(ShipOrderInterface::class);

        $shipOrder->execute($order->getEntityId(), [$this->createShipmentItem($itemId, 1.0)]);
        $order = $this->getOrder();
        $this->assertSame(Order::STATE_PROCESSING, $order->getState());
        $this->assertTrue($order->canShip());

        $shipOrder->execute($order->getEntityId(), [$this->createShipmentItem($itemId, 0.5)]);
        $order = $this->getOrder();
        $this->assertSame(Order::STATE_COMPLETE, $order->getState());
        $this->assertFalse($order->canShip());
    }

    /**
     * @param int $orderItemId
     * @param float $qty
     * @return ShipmentItemCreationInterface
     */
    private function createShipmentItem(int $orderItemId, float $qty): ShipmentItemCreationInterface
    {
        $item = Bootstrap::getObjectManager()->create(ShipmentItemCreationInterface::class);
        $item->setOrderItemId($orderItemId);
        $item->setQty($qty);

        return $item;
    }

    /**
     * @return OrderInterface
     */
    private function getOrder(): OrderInterface
    {
        $orderId = (int)Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)
            ->getStorage()->get('order')->getEntityId();

        return Bootstrap::getObjectManager()->create(OrderRepositoryInterface::class)->get($orderId);
    }
}
