<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Report;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\Flag;
use Magento\Reports\Model\FlagFactory;
use Magento\Sales\Model\ResourceModel\Helper;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BestsellersTest extends TestCase
{
    /**
     * @var Product|MockObject
     */
    protected Product $_productResource;

    /**
     * @var Helper|MockObject
     */
    protected Helper $_salesResourceHelper;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Bestsellers
     */
    protected Bestsellers $report;

    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $time;

    /**
     * @var FlagFactory
     */
    protected FlagFactory $flagFactory;

    /**
     * @var Validator
     */
    protected Validator $validator;

    /**
     * @var DateTime
     */
    protected DateTime $date;

    /**
     * @var Product
     */
    protected Product $product;

    /**
     * @var Helper
     */
    protected Helper $helper;

    /**
     * @var string
     */
    protected string $connectionName = 'connection_name';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->time = $this->createMock(TimezoneInterface::class);
        $this->flagFactory = $this->createMock(FlagFactory::class);
        $this->validator = $this->createMock(Validator::class);
        $this->date = $this->createMock(DateTime::class);
        $this->product = $this->createMock(Product::class);
        $this->helper = $this->createMock(Helper::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        parent::setUp();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testAggregatePerStoreCalculationWithInterval(): void
    {
        $from = new \DateTime('yesterday');
        $to = new \DateTime();
        $periodExpr = 'DATE(DATE_ADD(`source_table`.`created_at`, INTERVAL -25200 SECOND))';
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->exactly(2))->method('group');
        $select->expects($this->exactly(5))->method('from')->willReturn($select);
        $select->expects($this->exactly(3))->method('distinct')->willReturn($select);
        $select->expects($this->once())->method('joinInner')->willReturn($select);
        $select->expects($this->once())->method('joinLeft')->willReturn($select);
        $select->expects($this->any())->method('where')->willReturn($select);
        $select->expects($this->once())->method('useStraightJoin');
        $select->expects($this->exactly(2))->method('insertFromSelect');
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->exactly(4))
            ->method('getDatePartSql')
            ->willReturn($periodExpr);
        $connection->expects($this->any())->method('select')->willReturn($select);
        $query = $this->createMock(\Zend_Db_Statement_Interface::class);
        $connection->expects($this->exactly(3))->method('query')->willReturn($query);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->any())
            ->method('getConnection')
            ->with($this->connectionName)
            ->willReturn($connection);
        $this->context->expects($this->any())->method('getResources')->willReturn($resource);

        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStores')->with(true)->willReturn([$store]);

        $this->helper->expects($this->exactly(3))->method('getBestsellersReportUpdateRatingPos');

        $flag = $this->createMock(Flag::class);
        $flag->expects($this->once())->method('setReportFlagCode')->willReturn($flag);
        $flag->expects($this->once())->method('unsetData')->willReturn($flag);
        $flag->expects($this->once())->method('loadSelf');
        $this->flagFactory->expects($this->once())->method('create')->willReturn($flag);

        $date = $this->createMock(\DateTime::class);
        $date->expects($this->exactly(4))->method('format')->with('e');
        $this->time->expects($this->exactly(4))->method('scopeDate')->willReturn($date);

        $this->report = new Bestsellers(
            $this->context,
            $this->logger,
            $this->time,
            $this->flagFactory,
            $this->validator,
            $this->date,
            $this->product,
            $this->helper,
            $this->connectionName,
            [],
            $this->storeManager
        );

        $this->report->aggregate($from, $to);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testAggregatePerStoreCalculationNoInterval(): void
    {
        $periodExpr = 'DATE(DATE_ADD(`source_table`.`created_at`, INTERVAL -25200 SECOND))';
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->exactly(2))->method('group');
        $select->expects($this->exactly(3))->method('from')->willReturn($select);
        $select->expects($this->once())->method('joinInner')->willReturn($select);
        $select->expects($this->once())->method('joinLeft')->willReturn($select);
        $select->expects($this->exactly(3))->method('where')->willReturn($select);
        $select->expects($this->once())->method('useStraightJoin');
        $select->expects($this->exactly(2))->method('insertFromSelect');
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())
            ->method('getDatePartSql')
            ->willReturn($periodExpr);
        $connection->expects($this->any())->method('select')->willReturn($select);
        $connection->expects($this->exactly(2))->method('query');
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->any())
            ->method('getConnection')
            ->with($this->connectionName)
            ->willReturn($connection);
        $this->context->expects($this->any())->method('getResources')->willReturn($resource);

        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStores')->with(true)->willReturn([$store]);

        $this->helper->expects($this->exactly(3))->method('getBestsellersReportUpdateRatingPos');

        $flag = $this->createMock(Flag::class);
        $flag->expects($this->once())->method('setReportFlagCode')->willReturn($flag);
        $flag->expects($this->once())->method('unsetData')->willReturn($flag);
        $flag->expects($this->once())->method('loadSelf');
        $this->flagFactory->expects($this->once())->method('create')->willReturn($flag);

        $date = $this->createMock(\DateTime::class);
        $date->expects($this->once())->method('format')->with('e');
        $this->time->expects($this->once())->method('scopeDate')->willReturn($date);

        $this->report = new Bestsellers(
            $this->context,
            $this->logger,
            $this->time,
            $this->flagFactory,
            $this->validator,
            $this->date,
            $this->product,
            $this->helper,
            $this->connectionName,
            [],
            $this->storeManager
        );

        $this->report->aggregate();
    }
}
