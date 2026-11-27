<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Report;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Test\Fixture\PlaceOrderWithCustomerOrGuest as OrderFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class BestsellersTest extends TestCase
{
    private const STALE_RATING = 99;

    private const REFRESHED_DAY_FROM = '2025-01-20 00:00:00';

    private const REFRESHED_DAY_TO = '2025-01-20 23:59:59';

    /**
     * Periods containing the refreshed day, per aggregation table
     */
    private const AFFECTED_PERIODS = [
        'sales_bestsellers_aggregated_daily' => ['2025-01-20'],
        'sales_bestsellers_aggregated_monthly' => ['2025-01-01'],
        'sales_bestsellers_aggregated_yearly' => ['2025-01-01'],
    ];

    #[
        DbIsolation(true),
        Config('general/locale/timezone', 'UTC'),
        DataFixture(ProductFixture::class, as: 'product_a'),
        DataFixture(ProductFixture::class, as: 'product_b'),
        DataFixture(
            OrderFixture::class,
            [
                'customer_is_guest' => true,
                'items' => [['sku' => '$product_a.sku$', 'qty' => 1]],
            ],
            'order_december'
        ),
        DataFixture(
            OrderFixture::class,
            [
                'customer_is_guest' => true,
                'items' => [['sku' => '$product_a.sku$', 'qty' => 2], ['sku' => '$product_b.sku$', 'qty' => 5]],
            ],
            'order_january_10'
        ),
        DataFixture(
            OrderFixture::class,
            [
                'customer_is_guest' => true,
                'items' => [['sku' => '$product_a.sku$', 'qty' => 4], ['sku' => '$product_b.sku$', 'qty' => 2]],
            ],
            'order_january_20'
        ),
    ]
    public function testAggregateByDateRangeUpdatesRatingOnlyForPeriodsContainingTheRange(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $report = Bootstrap::getObjectManager()->create(Bestsellers::class);
        $this->setOrderDate((int)$fixtures->get('order_december')->getEntityId(), '2024-12-15 12:00:00');
        $this->setOrderDate((int)$fixtures->get('order_january_10')->getEntityId(), '2025-01-10 12:00:00');
        $this->setOrderDate((int)$fixtures->get('order_january_20')->getEntityId(), '2025-01-20 12:00:00');
        $productIds = [(int)$fixtures->get('product_a')->getId(), (int)$fixtures->get('product_b')->getId()];

        $report->aggregate();
        $fullRefresh = $this->getRatings($productIds);
        foreach (array_keys(self::AFFECTED_PERIODS) as $table) {
            $this->getConnection()->update(
                $this->getTableName($table),
                ['rating_pos' => self::STALE_RATING]
            );
        }

        $report->aggregate(self::REFRESHED_DAY_FROM, self::REFRESHED_DAY_TO);
        $rangeRefresh = $this->getRatings($productIds);

        foreach (self::AFFECTED_PERIODS as $table => $affectedPeriods) {
            $this->assertSame(array_keys($fullRefresh[$table]), array_keys($rangeRefresh[$table]), $table);
            $periods = array_unique(array_column($fullRefresh[$table], 'period'));
            $this->assertGreaterThan(1, count($periods), $table . ' must hold periods outside the range');
            foreach ($fullRefresh[$table] as $key => $row) {
                $expected = in_array($row['period'], $affectedPeriods, true)
                    ? $row['rating_pos']
                    : self::STALE_RATING;
                $this->assertSame($expected, $rangeRefresh[$table][$key]['rating_pos'], $table . ' ' . $key);
                $this->assertSame(
                    $row['qty_ordered'],
                    $rangeRefresh[$table][$key]['qty_ordered'],
                    $table . ' ' . $key
                );
            }
        }
    }

    /**
     * Move an order to the given date and give it the base-to-global rate the report multiplies by
     *
     * @param int $orderId
     * @param string $date
     * @return void
     */
    private function setOrderDate(int $orderId, string $date): void
    {
        $this->getConnection()->update(
            $this->getTableName('sales_order'),
            ['created_at' => $date, 'updated_at' => $date, 'base_to_global_rate' => 1],
            ['entity_id = ?' => $orderId]
        );
    }

    /**
     * Read the ordered quantities and rating positions of the given products from every aggregation table
     *
     * @param int[] $productIds
     * @return array
     */
    private function getRatings(array $productIds): array
    {
        $ratings = [];
        foreach (array_keys(self::AFFECTED_PERIODS) as $table) {
            $select = $this->getConnection()->select()
                ->from(
                    $this->getTableName($table),
                    ['period', 'store_id', 'product_id', 'qty_ordered', 'rating_pos']
                )
                ->where('product_id IN (?)', $productIds)
                ->order(['period', 'store_id', 'product_id']);
            $ratings[$table] = [];
            foreach ($this->getConnection()->fetchAll($select) as $row) {
                $key = $row['period'] . '/' . $row['store_id'] . '/' . $row['product_id'];
                $ratings[$table][$key] = [
                    'period' => $row['period'],
                    'qty_ordered' => (string)$row['qty_ordered'],
                    'rating_pos' => (int)$row['rating_pos'],
                ];
            }
        }

        return $ratings;
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection('sales');
    }

    /**
     * @param string $table
     * @return string
     */
    private function getTableName(string $table): string
    {
        return Bootstrap::getObjectManager()->get(ResourceConnection::class)->getTableName($table);
    }
}
