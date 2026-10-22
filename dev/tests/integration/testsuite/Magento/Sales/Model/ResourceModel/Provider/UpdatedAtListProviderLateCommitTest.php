<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Provider;

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
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Grid;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * A row whose transaction commits after a reconciliation run has read the table becomes visible with an
 * `updated_at` that is already below the watermark saved by that run.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdatedAtListProviderLateCommitTest extends TestCase
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var Grid
     */
    private $grid;

    /**
     * @var LastUpdateTimeCache
     */
    private $lastUpdateTimeCache;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->connection = $objectManager->get(ResourceConnection::class)->getConnection('sales');
        $this->grid = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Grid');
        $this->lastUpdateTimeCache = $objectManager->get(LastUpdateTimeCache::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    protected function tearDown(): void
    {
        $this->lastUpdateTimeCache->remove($this->grid->getGridTable());
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testRowCommittedBelowWatermarkIsSynced(): void
    {
        $entityId = $this->simulateLateCommit();

        $this->grid->refreshBySchedule();

        self::assertSame(Order::STATE_HOLDED, $this->getGridStatus($entityId));
    }

    #[
        Config('dev/grid/async_indexing_lookback', 0),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testRowCommittedBelowWatermarkIsSkippedWithoutLookback(): void
    {
        $entityId = $this->simulateLateCommit();

        $this->grid->refreshBySchedule();

        self::assertNotSame(Order::STATE_HOLDED, $this->getGridStatus($entityId));
    }

    /**
     * Put the order in the state left behind by a transaction that committed after the previous run's read.
     *
     * @return int
     */
    private function simulateLateCommit(): int
    {
        $entityId = (int)$this->fixtures->get('order')->getEntityId();
        $this->grid->refresh($entityId);

        $gridTable = $this->grid->getGridTable();
        $this->connection->update(
            $gridTable,
            ['updated_at' => $this->secondsAgo(120)],
            ['entity_id = ?' => $entityId]
        );
        $this->connection->update(
            $this->connection->getTableName('sales_order'),
            ['state' => Order::STATE_HOLDED, 'status' => Order::STATE_HOLDED, 'updated_at' => $this->secondsAgo(60)],
            ['entity_id = ?' => $entityId]
        );
        $this->lastUpdateTimeCache->save($gridTable, $this->secondsAgo(30));

        self::assertNotSame(Order::STATE_HOLDED, $this->getGridStatus($entityId), 'precondition: grid is stale');

        return $entityId;
    }

    private function secondsAgo(int $seconds): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->sub(new \DateInterval('PT' . $seconds . 'S'))
            ->format('Y-m-d H:i:s');
    }

    private function getGridStatus(int $entityId): string
    {
        $select = $this->connection->select()
            ->from($this->grid->getGridTable(), ['status'])
            ->where('entity_id = ?', $entityId);

        return (string)$this->connection->fetchOne($select);
    }
}
