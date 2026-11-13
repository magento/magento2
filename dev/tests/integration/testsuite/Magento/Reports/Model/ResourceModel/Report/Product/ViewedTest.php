<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Model\ResourceModel\Report\Product;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\Event;
use Magento\Reports\Model\EventFactory;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea('adminhtml'), DbIsolation(true)]
class ViewedTest extends TestCase
{
    private const GUEST_SUBTYPE = 1;
    private const CUSTOMER_SUBTYPE = 0;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var string
     */
    private $loggedAt;

    /**
     * @var string
     */
    private $period;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->get(ResourceConnection::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();

        $loggedAt = (new \DateTime('now', new \DateTimeZone('UTC')))->modify('-10 days')->setTime(12, 0);
        $this->loggedAt = $loggedAt->format('Y-m-d H:i:s');
        $this->period = $this->objectManager->get(TimezoneInterface::class)->date($loggedAt)->format('Y-m-d');
    }

    #[DataFixture(ProductFixture::class, as: 'product')]
    public function testLifetimeRefreshKeepsViewsOfPurgedGuestEvents(): void
    {
        $productId = (int)$this->fixtures->get('product')->getId();
        $this->logViews($productId, self::GUEST_SUBTYPE, 3);
        $this->logViews($productId, self::CUSTOMER_SUBTYPE, 1);

        $this->createViewedResource()->aggregate();
        $this->assertEquals(4, $this->getDailyViews($productId));

        $this->purgeGuestEvents();
        $this->createViewedResource()->aggregate();

        $this->assertEquals(4, $this->getDailyViews($productId));
        $this->assertEquals(4, $this->getMonthlyViews($productId));
    }

    #[DataFixture(ProductFixture::class, as: 'product')]
    public function testRangeRefreshKeepsViewsOfPurgedGuestEvents(): void
    {
        $productId = (int)$this->fixtures->get('product')->getId();
        $this->logViews($productId, self::GUEST_SUBTYPE, 3);
        $this->logViews($productId, self::CUSTOMER_SUBTYPE, 1);
        $from = (new \DateTime($this->loggedAt))->modify('-1 day');

        $this->createViewedResource()->aggregate($from);
        $this->assertEquals(4, $this->getDailyViews($productId));

        $this->purgeGuestEvents();
        $this->createViewedResource()->aggregate($from);

        $this->assertEquals(4, $this->getDailyViews($productId));
    }

    #[DataFixture(ProductFixture::class, as: 'product')]
    public function testLifetimeRefreshCountsNewViews(): void
    {
        $productId = (int)$this->fixtures->get('product')->getId();
        $this->logViews($productId, self::CUSTOMER_SUBTYPE, 2);
        $this->createViewedResource()->aggregate();
        $this->assertEquals(2, $this->getDailyViews($productId));

        $this->logViews($productId, self::CUSTOMER_SUBTYPE, 3);
        $this->createViewedResource()->aggregate();

        $this->assertEquals(5, $this->getDailyViews($productId));
    }

    /**
     * @param int $productId
     * @param int $subtype
     * @param int $count
     * @return void
     */
    private function logViews(int $productId, int $subtype, int $count): void
    {
        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'logged_at' => $this->loggedAt,
                'event_type_id' => Event::EVENT_PRODUCT_VIEW,
                'object_id' => $productId,
                'subject_id' => 0,
                'subtype' => $subtype,
                'store_id' => 1,
            ];
        }
        $this->resource->getConnection()->insertMultiple($this->resource->getTableName('report_event'), $rows);
    }

    /**
     * Run the cleanup executed after the visitor log cleaning: it removes guest events of deleted visitors
     *
     * @return void
     */
    private function purgeGuestEvents(): void
    {
        $this->objectManager->get(EventFactory::class)->create()->clean();
    }

    /**
     * @return Viewed
     */
    private function createViewedResource(): Viewed
    {
        return $this->objectManager->create(Viewed::class);
    }

    /**
     * @param int $productId
     * @return int
     */
    private function getDailyViews(int $productId): int
    {
        return $this->getViews(Viewed::AGGREGATION_DAILY, $productId, $this->period);
    }

    /**
     * @param int $productId
     * @return int
     */
    private function getMonthlyViews(int $productId): int
    {
        return $this->getViews(Viewed::AGGREGATION_MONTHLY, $productId, substr($this->period, 0, 8) . '01');
    }

    /**
     * @param string $table
     * @param int $productId
     * @param string $period
     * @return int
     */
    private function getViews(string $table, int $productId, string $period): int
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName($table), ['views_num'])
            ->where('product_id = ?', $productId)
            ->where('store_id = ?', 1)
            ->where('period = ?', $period);

        return (int)$connection->fetchOne($select);
    }
}
