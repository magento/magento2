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
        $entityFactoryMock = $this->getMock(\Magento\Framework\Data\Collection\EntityFactoryInterface::class);
        $loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $fetchStrategyMock = $this->getMock(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class);
        $managerMock = $this->getMock(\Magento\Framework\Event\ManagerInterface::class);
        $connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [], [], '', false);
        $selectRendererMock = $this->getMock(\Magento\Framework\DB\Select\SelectRenderer::class, [], [], '', false);
        $resourceMock = $this->getMock(\Magento\Framework\Flag\FlagResource::class, [], [], '', false);
        $resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $selectMock = $this->getMock(
            \Magento\Framework\DB\Select::class,
            ['getPart', 'setPart', 'from', 'columns'],
            [$connectionMock, $selectRendererMock]
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
            \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection::class,
            $this->model
        );
        $this->assertEquals(
            \Magento\Indexer\Model\Indexer\State::class,
            $this->model->getModelName()
        );
        $this->assertEquals(
            \Magento\Indexer\Model\ResourceModel\Indexer\State::class,
            $this->model->getResourceModelName()
        );
    }
}
