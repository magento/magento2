<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Report\Product;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\Flag;
use Magento\Reports\Model\FlagFactory;
use Magento\Reports\Model\ResourceModel\Helper;
use Magento\Reports\Model\ResourceModel\Report\Product\Viewed;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewedTest extends TestCase
{
    /**
     * @var Viewed
     */
    protected $viewed;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezoneMock;

    /**
     * @var FlagFactory|MockObject
     */
    protected $flagFactoryMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Helper|MockObject
     */
    protected $helperMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * @var \Zend_Db_Statement_Interface|MockObject
     */
    protected $zendDbMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    protected $attributeMock;

    /**
     * @var AbstractBackend|MockObject
     */
    protected $backendMock;

    /**
     * @var Flag|MockObject
     */
    protected $flagMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp(): void
    {
        $this->zendDbMock = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)->getMock();
        $this->zendDbMock->expects($this->any())->method('fetchColumn')->willReturn([]);

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'from',
                    'where',
                    'joinInner',
                    'joinLeft',
                    'having',
                    'useStraightJoin',
                    'insertFromSelect',
                    '__toString'
                ]
            )
            ->getMock();
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('joinInner')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('joinLeft')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('having')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('useStraightJoin')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('insertFromSelect')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('__toString')->willReturn('string');

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);
        $this->connectionMock->expects($this->any())->method('query')->willReturn($this->zendDbMock);

        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->any())->method('getTableName')->willReturnCallback(
            function ($arg) {
                return $arg;
            }
        );

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getResources')->willReturn($this->resourceMock);

        $dateTime = $this->getMockBuilder(\DateTime::class)->getMock();

        $this->timezoneMock = $this->getMockBuilder(
            TimezoneInterface::class
        )->getMock();
        $this->timezoneMock->expects($this->any())->method('scopeDate')->willReturn($dateTime);

        $this->flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->setMethods(['setReportFlagCode', 'unsetData', 'loadSelf', 'setFlagData', 'setLastUpdate', 'save'])
            ->getMock();

        $this->flagFactoryMock = $this->getMockBuilder(FlagFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->flagFactoryMock->expects($this->any())->method('create')->willReturn($this->flagMock);

        $this->backendMock = $this->getMockBuilder(AbstractBackend::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMock->expects($this->any())->method('getBackend')->willReturn($this->backendMock);

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);

        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewed = (new ObjectManager($this))->getObject(
            Viewed::class,
            [
                'context' => $this->contextMock,
                'localeDate' => $this->timezoneMock,
                'reportsFlagFactory' => $this->flagFactoryMock,
                'productResource' => $this->productMock,
                'resourceHelper' => $this->helperMock,
            ]
        );
    }

    /**
     * @param mixed $from
     * @param mixed $to
     * @param InvokedCount $truncateCount
     * @param InvokedCount $deleteCount
     * @dataProvider intervalsDataProvider
     * @return void
     */
    public function testAggregate($from, $to, $truncateCount, $deleteCount)
    {
        $this->connectionMock->expects($truncateCount)->method('truncateTable');
        $this->connectionMock->expects($deleteCount)->method('delete');

        $this->helperMock
            ->expects($this->at(0))
            ->method('updateReportRatingPos')
            ->with(
                $this->connectionMock,
                'day',
                'views_num',
                'report_viewed_product_aggregated_daily',
                'report_viewed_product_aggregated_daily'
            )
            ->willReturnSelf();
        $this->helperMock
            ->expects($this->at(1))
            ->method('updateReportRatingPos')
            ->with(
                $this->connectionMock,
                'month',
                'views_num',
                'report_viewed_product_aggregated_daily',
                'report_viewed_product_aggregated_monthly'
            )
            ->willReturnSelf();
        $this->helperMock
            ->expects($this->at(2))
            ->method('updateReportRatingPos')
            ->with(
                $this->connectionMock,
                'year',
                'views_num',
                'report_viewed_product_aggregated_daily',
                'report_viewed_product_aggregated_yearly'
            )
            ->willReturnSelf();

        $this->flagMock->expects($this->once())->method('unsetData')->willReturnSelf();
        $this->flagMock->expects($this->once())->method('loadSelf')->willReturnSelf();
        $this->flagMock->expects($this->never())->method('setFlagData')->willReturnSelf();
        $this->flagMock->expects($this->once())->method('save')->willReturnSelf();
        $this->flagMock
            ->expects($this->once())
            ->method('setReportFlagCode')
            ->with(Flag::REPORT_PRODUCT_VIEWED_FLAG_CODE)
            ->willReturnSelf();

        $this->viewed->aggregate($from, $to);
    }

    /**
     * @return array
     */
    public function intervalsDataProvider()
    {
        return [
            [
                'from' => new \DateTime('+3 day'),
                'to' => new \DateTime('-3 day'),
                'truncateCount' => $this->never(),
                'deleteCount' => $this->once()
            ],
            [
                'from' => null,
                'to' => null,
                'truncateCount' => $this->once(),
                'deleteCount' => $this->never()
            ]
        ];
    }
}
