<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\ResourceModel\Indexer\State;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection
     */
    protected $model;

    public function testConstruct()
    {
        $entityFactoryMock = $this->getMock('Magento\Framework\Data\Collection\EntityFactoryInterface');
        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $fetchStrategyMock = $this->getMock('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');
        $managerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $resourceMock = $this->getMock('Magento\Framework\Flag\FlagResource', [], [], '', false);
        $resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            ['getPart', 'setPart', 'from', 'columns'],
            [$connectionMock]
        );
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));

        $this->model = new \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection(
            $entityFactoryMock,
            $loggerMock,
            $fetchStrategyMock,
            $managerMock,
            $connectionMock,
            $resourceMock
        );

        $this->assertInstanceOf(
            'Magento\Indexer\Model\ResourceModel\Indexer\State\Collection',
            $this->model
        );
        $this->assertEquals(
            'Magento\Indexer\Model\Indexer\State',
            $this->model->getModelName()
        );
        $this->assertEquals(
            'Magento\Indexer\Model\ResourceModel\Indexer\State',
            $this->model->getResourceModelName()
        );
    }
}
