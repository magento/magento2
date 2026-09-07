<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\ResourceModel\Indexer\State;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Flag\FlagResource;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\ResourceModel\Indexer\State\Collection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $model;

    public function testConstruct()
    {
        $entityFactoryMock = $this->createMock(EntityFactoryInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $fetchStrategyMock = $this->createMock(FetchStrategyInterface::class);
        $managerMock = $this->createMock(ManagerInterface::class);
        $connectionMock = $this->createMock(Mysql::class);
        $selectRendererMock = $this->createMock(SelectRenderer::class);
        $resourceMock = $this->createMock(FlagResource::class);
        $resourceMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $selectMock = $this->getMockBuilder(Select::class)
            ->onlyMethods(['getPart', 'setPart', 'from', 'columns'])
            ->setConstructorArgs([$connectionMock, $selectRendererMock])
            ->getMock();
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);

        $this->model = new Collection(
            $entityFactoryMock,
            $loggerMock,
            $fetchStrategyMock,
            $managerMock,
            $connectionMock,
            $resourceMock
        );

        $this->assertInstanceOf(
            Collection::class,
            $this->model
        );
        $this->assertEquals(
            State::class,
            $this->model->getModelName()
        );
        $this->assertEquals(
            \Magento\Indexer\Model\ResourceModel\Indexer\State::class,
            $this->model->getResourceModelName()
        );
    }
}
