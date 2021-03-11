<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\ResourceModel\Mview\View\State;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection
     */
    protected $model;

    public function testConstruct()
    {
        $entityFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\EntityFactoryInterface::class);
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $fetchStrategyMock = $this->createMock(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class);
        $managerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $selectRendererMock = $this->createMock(\Magento\Framework\DB\Select\SelectRenderer::class);
        $resourceMock = $this->createMock(\Magento\Framework\Flag\FlagResource::class);
        $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->setMethods(['getPart', 'setPart', 'from', 'columns'])
            ->setConstructorArgs([$connectionMock, $selectRendererMock])
            ->getMock();
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);

        $this->model = new \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection(
            $entityFactoryMock,
            $loggerMock,
            $fetchStrategyMock,
            $managerMock,
            $connectionMock,
            $resourceMock
        );

        $this->assertInstanceOf(
            \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection::class,
            $this->model
        );
        $this->assertEquals(
            \Magento\Indexer\Model\Mview\View\State::class,
            $this->model->getModelName()
        );
        $this->assertEquals(
            \Magento\Indexer\Model\ResourceModel\Mview\View\State::class,
            $this->model->getResourceModelName()
        );
    }
}
