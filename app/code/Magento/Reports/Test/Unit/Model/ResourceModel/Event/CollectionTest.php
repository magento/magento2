<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Event;

use Magento\Reports\Model\ResourceModel\Event\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Event\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMockBuilder('Magento\Framework\Data\Collection\EntityFactoryInterface')
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMock();
        $this->fetchStrategyMock = $this->getMockBuilder('Magento\Framework\Data\Collection\Db\FetchStrategyInterface')
            ->getMock();
        $this->managerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->getMock();

        $this->selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['where', 'from'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $this->selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $this->dbMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getCurrentStoreIds', '_construct', 'getMainTable', 'getTable'])
            ->getMock();
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->dbMock);

        $this->collection = new Collection(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->managerMock,
            null,
            $this->resourceMock
        );
    }

    /**
     * @param mixed $ignoreData
     * @param 'string' $ignoreSql
     * @dataProvider ignoresDataProvider
     * @return void
     */
    public function testAddStoreFilter($ignoreData, $ignoreSql)
    {
        $typeId = 1;
        $subjectId =2;
        $subtype = 3;
        $limit = 0;
        $stores = [1, 2];

        $this->resourceMock
            ->expects($this->once())
            ->method('getCurrentStoreIds')
            ->willReturn($stores);
        $this->selectMock
            ->expects($this->at(0))
            ->method('where')
            ->with('event_type_id = ?', $typeId);
        $this->selectMock
            ->expects($this->at(1))
            ->method('where')
            ->with('subject_id = ?', $subjectId);
        $this->selectMock
            ->expects($this->at(2))
            ->method('where')
            ->with('subtype = ?', $subtype);
        $this->selectMock
            ->expects($this->at(3))
            ->method('where')
            ->with('store_id IN(?)', $stores);
        $this->selectMock
            ->expects($this->at(4))
            ->method('where')
            ->with($ignoreSql, $ignoreData);

        $this->collection->addRecentlyFiler($typeId, $subjectId, $subtype, $ignoreData, $limit);
    }

    /**
     * @return array
     */
    public function ignoresDataProvider()
    {
        return [
            [
                'ignoreData' => 1,
                'ignoreSql' => 'object_id <> ?'
            ],
            [
                'ignoreData' => [1],
                'ignoreSql' => 'object_id NOT IN(?)'
            ]
        ];
    }
}
