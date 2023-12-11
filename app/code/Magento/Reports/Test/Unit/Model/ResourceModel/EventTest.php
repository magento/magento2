<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Reports\Model\ResourceModel\Event;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();

        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->contextMock
            ->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourceMock);

        $this->event = new Event(
            $this->contextMock,
            $this->scopeConfigMock,
            $this->storeManagerMock
        );
    }

    /**
     * @return void
     */
    public function testUpdateCustomerTypeWithoutType(): void
    {
        $eventMock = $this->getMockBuilder(\Magento\Reports\Model\Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock
            ->expects($this->never())
            ->method('update');

        $this->event->updateCustomerType($eventMock, 1, 1);
    }

    /**
     * @return void
     */
    public function testUpdateCustomerTypeWithType(): void
    {
        $eventMock = $this->getMockBuilder(\Magento\Reports\Model\Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock
            ->expects($this->once())
            ->method('update');

        $this->event->updateCustomerType($eventMock, 1, 1, ['type']);
    }

    /**
     * @param int|null $storeId
     * @param array|null $storeIdSelect
     *
     * @return void
     * @dataProvider getApplyLogToCollectionDataProvider
     */
    public function testApplyLogToCollection(?int $storeId, ?array $storeIdSelect): void
    {
        $derivedSelect = 'SELECT * FROM table';
        $idFieldName = 'IdFieldName';

        $collectionSelectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['joinInner', 'order'])
            ->getMock();
        $collectionSelectMock
            ->expects($this->once())
            ->method('joinInner')
            ->with(
                ['evt' => new \Zend_Db_Expr("({$derivedSelect})")],
                "{$idFieldName} = evt.object_id",
                []
            )
            ->willReturnSelf();
        $collectionSelectMock
            ->expects($this->once())
            ->method('order')
            ->willReturnSelf();

        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->onlyMethods(['getResource', 'getIdFieldName', 'getSelect'])
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock
            ->expects($this->once())
            ->method('getResource')
            ->willReturnSelf();
        $collectionMock
            ->expects($this->once())
            ->method('getIdFieldName')
            ->willReturn($idFieldName);
        $collectionMock
            ->expects($this->any())
            ->method('getSelect')
            ->willReturn($collectionSelectMock);
        $collectionMock
            ->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', '__toString', 'from', 'group', 'joinInner'])
            ->getMock();
        $selectMock
            ->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('group')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('__toString')
            ->willReturn($derivedSelect);
        $selectMock
            ->expects($this->any())
            ->method('where')
            ->willReturnMap([
                ['event_type_id = ?', 1],
                ['subject_id = ?', 1],
                ['subtype = ?', 1],
                ['store_id IN(?)', $storeIdSelect]
            ]);

        $this->connectionMock
            ->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);

        $this->storeMock
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->event->applyLogToCollection($collectionMock, 1, 1, 1);
    }

    /**
     * @return array
     */
    public function getApplyLogToCollectionDataProvider(): array
    {
        return [
            ['storeId' => 1, 'storeIdSelect' => [1]],
            ['storeId' => null, 'storeIdSelect' => [1]]
        ];
    }
    /**
     * @return void
     */
    public function testClean(): void
    {
        $eventMock = $this->getMockBuilder(\Magento\Reports\Model\Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'limit', 'from', 'joinLeft'])
            ->addMethods(['select', 'fetchCol'])
            ->getMock();

        $this->connectionMock
            ->method('fetchCol')
            ->willReturnOnConsecutiveCalls(1);
        $this->connectionMock
            ->expects($this->any())
            ->method('delete');
        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $selectMock
            ->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('joinLeft')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('limit')
            ->willReturnSelf();

        $this->event->clean($eventMock);
    }
}
