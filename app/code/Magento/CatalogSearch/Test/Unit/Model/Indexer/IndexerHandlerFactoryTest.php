<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;

class IndexerHandlerFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var IndexerHandlerFactory */
    private $model;

    /** @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $objectManagerMock;

    /** @var EngineResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $engineResolverMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->engineResolverMock = $this->getMockBuilder(EngineResolverInterface::class)
            ->getMockForAbstractClass();
    }

    public function testCreate()
    {
        $currentHandler = 'current_handler';
        $currentHandlerClass = IndexerInterface::class;
        $handlers = [
            $currentHandler => $currentHandlerClass,
        ];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentHandler);

        $indexerMock = $this->getMockBuilder($currentHandlerClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentHandlerClass, $data)
            ->willReturn($indexerMock);

        $indexerMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->model = new IndexerHandlerFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $handlers
        );

        $this->assertEquals($indexerMock, $this->model->create($data));
    }

    /**
     */
    public function testCreateWithoutHandlers()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('There is no such indexer handler: current_handler');

        $currentHandler = 'current_handler';
        $handlers = [];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentHandler);

        $this->model = new IndexerHandlerFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $handlers
        );

        $this->model->create($data);
    }

    /**
     */
    public function testCreateWithWrongHandler()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('current_handler indexer handler doesn\'t implement');

        $currentHandler = 'current_handler';
        $currentHandlerClass = \stdClass::class;
        $handlers = [
            $currentHandler => $currentHandlerClass,
        ];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentHandler);

        $indexerMock = $this->getMockBuilder($currentHandlerClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentHandlerClass, $data)
            ->willReturn($indexerMock);

        $this->model = new IndexerHandlerFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $handlers
        );

        $this->model->create($data);
    }

    /**
     */
    public function testCreateWithoutAvailableHandler()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Indexer handler is not available: current_handler');

        $currentHandler = 'current_handler';
        $currentHandlerClass = IndexerInterface::class;
        $handlers = [
            $currentHandler => $currentHandlerClass,
        ];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentHandler);

        $indexerMock = $this->getMockBuilder($currentHandlerClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentHandlerClass, $data)
            ->willReturn($indexerMock);

        $indexerMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $this->model = new IndexerHandlerFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $handlers
        );

        $this->model->create($data);
    }
}
