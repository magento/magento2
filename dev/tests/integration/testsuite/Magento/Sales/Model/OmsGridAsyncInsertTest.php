<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Test\Fixture\Creditmemo as CreditmemoFixture;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test asynchronous grid indexing for OMS entities
 *
 * Verifies that entities are synced from main tables to grid tables
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class OmsGridAsyncInsertTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * Set up test dependencies
     *
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->connection = $this->resourceConnection->getConnection();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test Order async grid insert
     *
     * Verifies that:
     * - Order exists in sales_order table after creation
     * - Order does NOT exist in sales_order_grid table before async insert
     * - Order exists in sales_order_grid table after async insert
     * - Key fields match between main table and grid table
     *
     * @return void
     */
    #[
        Config('dev/grid/async_indexing', '1'),
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => 'guest@example.com']),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1
        ]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, [
            'cart_id' => '$cart.id$', 'carrier_code' => 'flatrate', 'method_code' => 'flatrate'
        ]),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testOrderAsyncGridInsert(): void
    {
        $orderId = (int)$this->fixtures->get('order')->getEntityId();

        // Verify order exists in main table but NOT in grid
        $this->assertEntityInMainTableButNotInGrid(
            'sales_order',
            'sales_order_grid',
            $orderId,
            'Order'
        );

        // Execute async grid insert directly
        $this->executeAsyncGridInsert('SalesOrderIndexGridAsyncInsert');

        // Verify order exists in both tables
        $this->assertEntityInBothMainTableAndGrid(
            'sales_order',
            'sales_order_grid',
            $orderId,
            ['increment_id', 'status', 'grand_total'],
            'Order'
        );
    }

    /**
     * Test Invoice async grid insert
     *
     * Verifies that:
     * - Invoice exists in sales_invoice table after creation
     * - Invoice does NOT exist in sales_invoice_grid table before async insert
     * - Invoice exists in sales_invoice_grid table after async insert
     * - Key fields match between main table and grid table
     *
     * @return void
     */
    #[
        Config('dev/grid/async_indexing', '1'),
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => 'guest@example.com']),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1
        ]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, [
            'cart_id' => '$cart.id$', 'carrier_code' => 'flatrate', 'method_code' => 'flatrate'
        ]),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
    ]
    public function testInvoiceAsyncGridInsert(): void
    {
        $invoiceId = (int)$this->fixtures->get('invoice')->getEntityId();

        // Verify invoice exists in main table but NOT in grid
        $this->assertEntityInMainTableButNotInGrid(
            'sales_invoice',
            'sales_invoice_grid',
            $invoiceId,
            'Invoice'
        );

        // Execute async grid insert directly
        $this->executeAsyncGridInsert('SalesInvoiceIndexGridAsyncInsert');

        // Verify invoice exists in both tables
        $this->assertEntityInBothMainTableAndGrid(
            'sales_invoice',
            'sales_invoice_grid',
            $invoiceId,
            ['increment_id', 'state', 'grand_total'],
            'Invoice'
        );
    }

    /**
     * Test Shipment async grid insert
     *
     * Verifies that:
     * - Shipment exists in sales_shipment table after creation
     * - Shipment does NOT exist in sales_shipment_grid table before async insert
     * - Shipment exists in sales_shipment_grid table after async insert
     * - Key fields match between main table and grid table
     *
     * @return void
     */
    #[
        Config('dev/grid/async_indexing', '1'),
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => 'guest@example.com']),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1
        ]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, [
            'cart_id' => '$cart.id$', 'carrier_code' => 'flatrate', 'method_code' => 'flatrate'
        ]),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$'], 'shipment'),
    ]
    public function testShipmentAsyncGridInsert(): void
    {
        $shipmentId = (int)$this->fixtures->get('shipment')->getEntityId();

        // Verify shipment exists in main table but NOT in grid
        $this->assertEntityInMainTableButNotInGrid(
            'sales_shipment',
            'sales_shipment_grid',
            $shipmentId,
            'Shipment'
        );

        // Execute async grid insert directly
        $this->executeAsyncGridInsert('SalesShipmentIndexGridAsyncInsert');

        // Verify shipment exists in both tables
        $this->assertEntityInBothMainTableAndGrid(
            'sales_shipment',
            'sales_shipment_grid',
            $shipmentId,
            ['increment_id', 'total_qty'],
            'Shipment'
        );
    }

    /**
     * Test Creditmemo async grid insert
     *
     * Verifies that:
     * - Creditmemo exists in sales_creditmemo table after creation
     * - Creditmemo does NOT exist in sales_creditmemo_grid table before async insert
     * - Creditmemo exists in sales_creditmemo_grid table after async insert
     * - Key fields match between main table and grid table
     *
     * @return void
     */
    #[
        Config('dev/grid/async_indexing', '1'),
        Config('payment/checkmo/active', '1'),
        Config('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => 'guest@example.com']),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1
        ]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, [
            'cart_id' => '$cart.id$', 'carrier_code' => 'flatrate', 'method_code' => 'flatrate'
        ]),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'checkmo']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(CreditmemoFixture::class, ['order_id' => '$order.id$'], 'creditmemo'),
    ]
    public function testCreditmemoAsyncGridInsert(): void
    {
        $creditmemoId = (int)$this->fixtures->get('creditmemo')->getEntityId();

        // Verify creditmemo exists in main table but NOT in grid
        $this->assertEntityInMainTableButNotInGrid(
            'sales_creditmemo',
            'sales_creditmemo_grid',
            $creditmemoId,
            'Creditmemo'
        );

        // Execute async grid insert directly
        $this->executeAsyncGridInsert('SalesCreditmemoIndexGridAsyncInsert');

        // Verify creditmemo exists in both tables
        $this->assertEntityInBothMainTableAndGrid(
            'sales_creditmemo',
            'sales_creditmemo_grid',
            $creditmemoId,
            ['increment_id', 'state', 'grand_total'],
            'Creditmemo'
        );
    }

    /**
     * Execute async grid insert directly (bypasses cron scheduling)
     *
     * This method calls the GridAsyncInsert service directly without going through
     * the cron scheduler. This is faster and more reliable for testing.
     *
     * @param string $virtualTypeName
     * @return void
     */
    private function executeAsyncGridInsert(string $virtualTypeName): void
    {
        $this->objectManager->get($virtualTypeName)->asyncInsert();
    }

    /**
     * Assert entity exists in main table but NOT in grid table
     *
     * Verifies the state before async grid insert has been executed.
     *
     * @param string $mainTable
     * @param string $gridTable
     * @param int $entityId
     * @param string $entityType
     * @return void
     */
    private function assertEntityInMainTableButNotInGrid(
        string $mainTable,
        string $gridTable,
        int    $entityId,
        string $entityType
    ): void {
        $this->assertNotEmpty(
            $this->getEntityFromTable($mainTable, $entityId),
            "{$entityType} {$entityId} should exist in {$mainTable} table"
        );

        $this->assertEmpty(
            $this->getEntityFromTable($gridTable, $entityId),
            "{$entityType} {$entityId} should NOT be in {$gridTable} yet (before async insert)"
        );
    }

    /**
     * Assert entity exists in both main table and grid table with matching data
     *
     * Verifies the state after async grid insert has been executed.
     *
     * @param string $mainTable
     * @param string $gridTable
     * @param int $entityId
     * @param array $fieldsToCompare
     * @param string $entityType
     * @return void
     */
    private function assertEntityInBothMainTableAndGrid(
        string $mainTable,
        string $gridTable,
        int    $entityId,
        array  $fieldsToCompare,
        string $entityType
    ): void {
        $entityInMainTable = $this->getEntityFromTable($mainTable, $entityId);
        $this->assertNotEmpty(
            $entityInMainTable,
            "{$entityType} {$entityId} should exist in {$mainTable} table"
        );

        $entityInGrid = $this->getEntityFromTable($gridTable, $entityId);
        $this->assertNotEmpty(
            $entityInGrid,
            "{$entityType} {$entityId} should be in {$gridTable} after async insert"
        );

        // Verify specified fields match between main table and grid
        foreach ($fieldsToCompare as $field) {
            if (isset($entityInMainTable[$field]) && isset($entityInGrid[$field])) {
                $this->assertEquals(
                    $entityInMainTable[$field],
                    $entityInGrid[$field],
                    "{$entityType} field '{$field}' should match between {$mainTable} and {$gridTable}"
                );
            }
        }
    }

    /**
     * Get entity data from specified table by entity ID
     *
     * @param string $tableName
     * @param int $entityId
     * @return array|null
     */
    private function getEntityFromTable(string $tableName, int $entityId): ?array
    {
        $select = $this->connection->select()
            ->from($this->resourceConnection->getTableName($tableName))
            ->where('entity_id = ?', $entityId);

        $row = $this->connection->fetchRow($select);
        return $row ?: null;
    }
}
