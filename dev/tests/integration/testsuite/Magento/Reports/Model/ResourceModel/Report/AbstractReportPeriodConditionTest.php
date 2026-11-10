<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Model\ResourceModel\Report;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractReportPeriodConditionTest extends TestCase
{
    private const FIRST_DAY = '2026-10-01 12:00:00';
    private const SECOND_DAY = '2026-10-02 12:00:00';
    private const STALE_QTY = 999;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var Bestsellers
     */
    private Bestsellers $bestsellers;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->connection = $objectManager->get(ResourceConnection::class)->getConnection();
        $this->bestsellers = $objectManager->create(Bestsellers::class);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart1'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart1.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart1.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart1.id$'], 'order1'),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart2.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart2.id$'], 'order2'),
    ]
    public function testAggregateRebuildsOnlyRequestedDay(): void
    {
        $this->setOrderDate((int)$this->fixtures->get('order1')->getId(), self::FIRST_DAY);
        $this->setOrderDate((int)$this->fixtures->get('order2')->getId(), self::SECOND_DAY);
        $productId = (int)$this->fixtures->get('product')->getId();

        $this->bestsellers->aggregate();
        $table = $this->bestsellers->getMainTable();
        $periods = $this->connection->fetchCol(
            "SELECT DISTINCT period FROM {$table} WHERE store_id <> 0 AND product_id = {$productId} ORDER BY period"
        );
        $this->assertCount(2, $periods);
        [$firstPeriod, $secondPeriod] = $periods;
        $this->connection->update($table, ['qty_ordered' => self::STALE_QTY], ['product_id = ?' => $productId]);

        $this->bestsellers->aggregate('2026-10-01 00:00:00', '2026-10-01 23:59:59');

        $this->assertSame(1, $this->qtyOrdered($productId, $firstPeriod));
        $this->assertSame(self::STALE_QTY, $this->qtyOrdered($productId, $secondPeriod));
    }

    private function setOrderDate(int $orderId, string $date): void
    {
        $this->connection->update(
            $this->connection->getTableName('sales_order'),
            ['created_at' => $date, 'updated_at' => $date],
            ['entity_id = ?' => $orderId]
        );
    }

    private function qtyOrdered(int $productId, string $period): int
    {
        return (int)$this->connection->fetchOne(
            $this->connection->select()
                ->from($this->bestsellers->getMainTable(), 'SUM(qty_ordered)')
                ->where('store_id <> 0')
                ->where('product_id = ?', $productId)
                ->where('period = ?', $period)
        );
    }
}
