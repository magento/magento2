<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Report\Order;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Sales\Model\ResourceModel\Report\Order\Createdat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatedatTest extends TestCase
{
    /**
     * @var Createdat
     */
    private Createdat $report;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $time;

    /**
     * @var FlagFactory
     */
    private FlagFactory $flagFactory;

    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var DateTime
     */
    private DateTime $date;

    /**
     * Data provider for testAggregateWithMultipleOrderDates
     *
     * @return array
     */
    public static function datesDataProvider(): array
    {
        $randomDates = [];
        for ($i = 0; $i < 10000; $i++) {
            $randomDates[] = date('Y-m-d', rand(0, time()));
        }
        return [
            'from-to interval' => [new \DateTime('yesterday'), new \DateTime(), $randomDates],
            'from interval' => [new \DateTime('yesterday'), null, $randomDates],
            'from interval no dates' => [new \DateTime('yesterday'), null, []]
        ];
    }

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

        parent::setUp();
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @param array $randomDates
     * @return void
     * @throws Exception
     */
    #[DataProvider('datesDataProvider')]
    public function testAggregateWithMultipleOrderDates(
        ?\DateTime $from,
        ?\DateTime $to,
        array $randomDates
    ): void {
        $periodExpr = 'DATE(DATE_ADD(`o`.`created_at`, INTERVAL -28800 SECOND))';
        $select = $this->createMock(Select::class);
        $select->expects($this->exactly(3))->method('group');
        $select->expects($this->exactly(4))->method('from')->willReturn($select);
        $select->expects($this->exactly(1))->method('distinct')->willReturn($select);
        $select->expects($this->once())->method('join')->willReturn($select);
        $select->expects($this->any())->method('where')->willReturn($select);
        $select->expects($this->exactly(2))->method('insertFromSelect');
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->exactly(2))
            ->method('getDatePartSql')
            ->willReturn($periodExpr);
        $connection->expects($this->any())->method('select')->willReturn($select);
        $query = $this->createMock(\Zend_Db_Statement_Interface::class);
        $query->expects($this->once())->method('fetchAll')->willReturn($randomDates);
        $connection->expects($this->exactly(3))->method('query')->willReturn($query);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);
        $this->context->expects($this->any())->method('getResources')->willReturn($resource);
        $date = $this->createMock(\DateTime::class);
        $date->expects($this->exactly(2))->method('format')->with('e');
        $this->time->expects($this->exactly(2))->method('scopeDate')->willReturn($date);

        $this->report = new Createdat(
            $this->context,
            $this->logger,
            $this->time,
            $this->flagFactory,
            $this->validator,
            $this->date,
        );

        $this->report->aggregate($from, $to);
    }
}
