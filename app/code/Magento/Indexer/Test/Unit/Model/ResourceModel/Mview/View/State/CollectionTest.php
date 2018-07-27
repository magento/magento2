<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\ResourceModel\Mview\View\State;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection
     */
    protected $model;

    public function testConstruct()
    {
        $entityFactoryMock = $this->getMock('Magento\Framework\Data\Collection\EntityFactoryInterface');
        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $fetchStrategyMock = $this->getMock('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');
        $managerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $selectRendererMock = $this->getMock('Magento\Framework\DB\Select\SelectRenderer', [], [], '', false);
        $resourceMock = $this->getMock('Magento\Framework\Flag\FlagResource', [], [], '', false);
        $resourceMock->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            ['getPart', 'setPart', 'from', 'columns'],
            [$connectionMock, $selectRendererMock]
        );
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));

        $this->model = new \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection(
            $entityFactoryMock,
            $loggerMock,
            $fetchStrategyMock,
            $managerMock,
            $connectionMock,
            $resourceMock
        );

        $this->assertInstanceOf(
            'Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection',
            $this->model
        );
        $this->assertEquals(
            'Magento\Indexer\Model\Mview\View\State',
            $this->model->getModelName()
        );
        $this->assertEquals(
            'Magento\Indexer\Model\ResourceModel\Mview\View\State',
            $this->model->getResourceModelName()
        );
    }
}
