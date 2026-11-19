<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Integration\Order;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\Sales\ViewModel\Order\CanceledItemQty;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration coverage for the Admin order cancellation flow.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminCancellationTest extends TestCase
{
    /**
     * Scenario: A partially-invoiced (2 of 3), fully-shipped (3 of 3) order is canceled, which the state
     *           machine downgrades to COMPLETE while still recording a canceled total.
     * Expected: The Qty column reports the uninvoiced remainder (ordered 3 - invoiced 2 = 1) as canceled,
     *           even though the order is not CANCELED and item qty_canceled was never populated.
     */
    #[
        DbIsolation(false),
        AppArea('adminhtml'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 100, 'is_in_stock' => 1]),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 3]
        ),
        DataFixture(
            SetBillingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(
            SetShippingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(
            InvoiceFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 2]]]
        ),
        DataFixture(
            ShipmentFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 3]]]
        ),
    ]
    public function testCancelPartiallyInvoicedFullyShippedOrderDisplaysUninvoicedQtyAsCanceled()
    {
        $order = $this->cancelAndReload($this->placedOrderId());

        $this->assertSame(
            OrderModel::STATE_COMPLETE,
            $order->getState(),
            'Cancelling a partially-invoiced order downgrades the state to COMPLETE, not CANCELED.'
        );
        $this->assertGreaterThan(
            0.0,
            (float) $order->getTotalCanceled(),
            'The order must record a canceled total even though its state is COMPLETE.'
        );

        $items = $order->getItems();
        $orderItem = reset($items);
        $this->assertSame(
            0.0,
            (float) $orderItem->getQtyCanceled(),
            'The item native qty_canceled stays 0 because the units were shipped, exposing the discrepancy.'
        );

        $this->assertSame(
            1.0,
            $this->displayCanceledQtyFor($order),
            '3 ordered - 2 invoiced = 1 uninvoiced unit must display as Canceled.'
        );
    }

    /**
     * Scenario: A fully-uninvoiced (0 of 3), fully-shipped (3 of 3) order is canceled; with no refundable
     *           invoiced qty it remains in the CANCELED state.
     * Expected: The Qty column reports every ordered unit (3 ordered - 0 invoiced = 3) as canceled.
     */
    #[
        DbIsolation(false),
        AppArea('adminhtml'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 100, 'is_in_stock' => 1]),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 3]
        ),
        DataFixture(
            SetBillingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(
            SetShippingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(
            ShipmentFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 3]]]
        ),
    ]
    public function testCancelFullyUninvoicedFullyShippedOrderDisplaysAllShippedQtyAsCanceled()
    {
        $order = $this->cancelAndReload($this->placedOrderId());

        $this->assertSame(
            OrderModel::STATE_CANCELED,
            $order->getState(),
            'With no refundable invoiced qty the canceled order stays in the CANCELED state.'
        );
        $this->assertGreaterThan(
            0.0,
            (float) $order->getTotalCanceled(),
            'The order must record a canceled total.'
        );

        $this->assertSame(
            3.0,
            $this->displayCanceledQtyFor($order),
            '3 ordered - 0 invoiced = 3 units must display as Canceled.'
        );
    }

    /**
     * Scenario: A partially-invoiced (1 of 4), partially-shipped (1 of 4) order is canceled, downgrading
     *           the state to COMPLETE and persisting the canceled remainder onto the item.
     * Expected: The Qty column display equals the persisted item qty_canceled (min(4-1, 4-1) = 3), proving
     *           no divergence on a partial shipment.
     */
    #[
        DbIsolation(false),
        AppArea('adminhtml'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 100, 'is_in_stock' => 1]),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 4]
        ),
        DataFixture(
            SetBillingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(
            SetShippingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(
            InvoiceFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 1]]]
        ),
        DataFixture(
            ShipmentFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 1]]]
        ),
    ]
    public function testCancelPartiallyShippedOrderDisplayMatchesPersistedCanceledQty()
    {
        $order = $this->cancelAndReload($this->placedOrderId());

        $this->assertSame(
            OrderModel::STATE_COMPLETE,
            $order->getState(),
            'Cancelling a partially-invoiced order downgrades the state to COMPLETE, not CANCELED.'
        );

        $items = $order->getItems();
        $orderItem = reset($items);
        $this->assertSame(
            3.0,
            (float) $orderItem->getQtyCanceled(),
            'The item persists the canceled remainder min(4-1, 4-1) = 3.'
        );

        $this->assertSame(
            3.0,
            $this->displayCanceledQtyFor($order),
            'On a partial shipment the display must equal the persisted qty_canceled (3) — no divergence.'
        );
    }

    /**
     * Scenario: A partially-invoiced (2 of 3) order is left open — never shipped and never canceled — so no
     *           canceled total is ever recorded.
     * Expected: The Qty column reports no canceled quantity, guarding against a false positive on an open
     *           order.
     */
    #[
        DbIsolation(false),
        AppArea('adminhtml'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 100, 'is_in_stock' => 1]),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 3]
        ),
        DataFixture(
            SetBillingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(
            SetShippingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(
            InvoiceFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 2]]]
        ),
    ]
    public function testOpenPartiallyInvoicedOrderDisplaysNoCanceledQty()
    {
        $orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $order = $orderRepository->get($this->placedOrderId());

        $this->assertSame(
            0.0,
            (float) $order->getTotalCanceled(),
            'An open order that was never canceled must not record a canceled total.'
        );

        $this->assertSame(
            0.0,
            $this->displayCanceledQtyFor($order),
            'An open partially-invoiced order must show no canceled quantity for its line item.'
        );
    }

    /**
     * Scenario: A fully-uninvoiced (0 of 5), partially-shipped (3 of 5) order is canceled; with no refundable
     *           invoiced qty it remains in the CANCELED state and persists the unshipped remainder.
     * Expected: The Qty column reports only the unshipped-uninvoiced units (5 ordered - 3 shipped = 2), matching
     *           the persisted qty_canceled, not every uninvoiced unit.
     */
    #[
        DbIsolation(false),
        AppArea('adminhtml'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 100, 'is_in_stock' => 1]),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 5]
        ),
        DataFixture(
            SetBillingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(
            SetShippingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(
            ShipmentFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 3]]]
        ),
    ]
    public function testCancelPartiallyShippedUninvoicedOrderDisplaysOnlyUnshippedQtyAsCanceled()
    {
        $order = $this->cancelAndReload($this->placedOrderId());

        $this->assertSame(
            OrderModel::STATE_CANCELED,
            $order->getState(),
            'With no refundable invoiced qty the canceled order stays in the CANCELED state.'
        );
        $this->assertGreaterThan(
            0.0,
            (float) $order->getTotalCanceled(),
            'The order must record a canceled total.'
        );

        $items = $order->getItems();
        $orderItem = reset($items);
        $this->assertSame(
            2.0,
            (float) $orderItem->getQtyCanceled(),
            'The item persists the unshipped remainder min(5-0, 5-3) = 2.'
        );

        $this->assertSame(
            2.0,
            $this->displayCanceledQtyFor($order),
            'Partial shipment must display only the unshipped-uninvoiced units (5 ordered - 3 shipped = 2) as '
            . 'canceled, not the shipped ones.'
        );
    }

    /**
     * Resolve the entity id of the order placed by the fixture set.
     *
     * @return int
     */
    private function placedOrderId(): int
    {
        return (int) DataFixtureStorageManager::getStorage()->get('order')->getEntityId();
    }

    /**
     * Cancel the given order through the order management service and reload it from the repository.
     *
     * @param int $orderId
     * @return OrderModel
     */
    private function cancelAndReload(int $orderId): OrderModel
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(OrderManagementInterface::class)->cancel($orderId);

        return $objectManager->get(OrderRepositoryInterface::class)->get($orderId);
    }

    /**
     * Resolve the displayed canceled quantity for the order's first line item via the view model.
     *
     * @param OrderModel $order
     * @return float
     */
    private function displayCanceledQtyFor(OrderModel $order): float
    {
        $items = $order->getItems();
        $orderItem = reset($items);

        $viewModel = Bootstrap::getObjectManager()->get(CanceledItemQty::class);

        return (float) $viewModel->getDisplayCanceledQty($orderItem);
    }
}
