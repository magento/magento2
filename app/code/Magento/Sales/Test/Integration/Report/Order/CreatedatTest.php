<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Integration\Report\Order;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\ResourceModel\Report\Order\Createdat;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration coverage for the Sales > Orders report aggregation of canceled orders.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatedatTest extends TestCase
{
    /**
     * Scenario: A partially-invoiced (2 of 3), fully-shipped (3 of 3) order is canceled, so the order records a
     *           canceled amount while the shipped line keeps qty_canceled = 0.
     * Expected: The aggregated Sales Items count follows the retained (invoiced) units, 2, instead of the
     *           ordered 3, and the canceled amount remains reported. Line item data is not modified.
     */
    #[
        DbIsolation(true),
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
    public function testCanceledPartiallyInvoicedShippedOrderCountsOnlyInvoicedUnits()
    {
        $orderId = $this->placedOrderId();
        Bootstrap::getObjectManager()->get(OrderManagementInterface::class)->cancel($orderId);

        $this->assertSame(
            0.0,
            $this->persistedQtyCanceled($orderId),
            'The report change must not populate the line item qty_canceled.'
        );

        $report = $this->aggregate();

        $this->assertEquals(2, $report['total_qty_ordered'], 'Sales Items must count only invoiced units.');
        $this->assertEquals(2, $report['total_qty_invoiced']);
        $this->assertGreaterThan(0, (float) $report['total_canceled_amount'], 'Canceled amount must stay reported.');
    }

    /**
     * Scenario: A fully-invoiced and fully-shipped order is not canceled.
     * Expected: Sales Items keeps counting the ordered units (3), so orders without a canceled amount are unchanged.
     */
    #[
        DbIsolation(true),
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
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 3]]]
        ),
        DataFixture(
            ShipmentFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 3]]]
        ),
    ]
    public function testNonCanceledOrderKeepsOrderedUnits()
    {
        $report = $this->aggregate();

        $this->assertEquals(3, $report['total_qty_ordered']);
        $this->assertEquals(3, $report['total_qty_invoiced']);
        $this->assertEquals(0, (float) $report['total_canceled_amount']);
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
     * Read the persisted qty_canceled of the order's line item.
     *
     * @param int $orderId
     * @return float
     */
    private function persistedQtyCanceled(int $orderId): float
    {
        $connection = Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('sales_order_item'), 'qty_canceled')
            ->where('order_id = ?', $orderId);

        return (float) $connection->fetchOne($select);
    }

    /**
     * Run the created-at aggregation and return the summed report row.
     *
     * Store 0 rows are the all-stores rollup of the per-store rows, so they are excluded to avoid double counting.
     *
     * @return array
     */
    private function aggregate(): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->create(Createdat::class)->aggregate();

        $connection = $objectManager->get(ResourceConnection::class)->getConnection();
        $select = $connection->select()->from(
            $connection->getTableName('sales_order_aggregated_created'),
            [
                'total_qty_ordered' => 'SUM(total_qty_ordered)',
                'total_qty_invoiced' => 'SUM(total_qty_invoiced)',
                'total_canceled_amount' => 'SUM(total_canceled_amount)',
            ]
        )->where('store_id <> 0');

        return $connection->fetchRow($select);
    }
}
