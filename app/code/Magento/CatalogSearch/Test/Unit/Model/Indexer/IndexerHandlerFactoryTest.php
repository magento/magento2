<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

class IndexerHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var IndexerHandlerFactory */
    protected $model;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    /** @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
    }

    public function testCreate()
    {
        $configPath = 'config_path';
        $currentHandler = 'current_handler';
        $currentHandlerClass = 'current_handler_class';
        $handlers = [
            $currentHandler => $currentHandlerClass,
        ];
        $data = ['data'];

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($configPath, ScopeInterface::SCOPE_STORE)
            ->willReturn($currentHandler);

        $indexerMock = $this->getMockBuilder(IndexerInterface::class)
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
            $this->scopeConfigMock,
            $configPath,
            $handlers
        );

        $this->assertEquals($indexerMock, $this->model->create($data));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no such indexer handler: current_handler
     */
    public function testCreateWithoutHandlers()
    {
        $configPath = 'config_path';
        $currentHandler = 'current_handler';
        $handlers = [];
        $data = ['data'];

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($configPath, ScopeInterface::SCOPE_STORE)
            ->willReturn($currentHandler);

        $this->model = new IndexerHandlerFactory(
            $this->objectManagerMock,
            $this->scopeConfigMock,
            $configPath,
            $handlers
        );

        $this->model->create($data);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage current_handler indexer handler doesn't implement
     */
    public function testCreateWithWrongHandler()
    {
        $configPath = 'config_path';
        $currentHandler = 'current_handler';
        $currentHandlerClass = 'current_handler_class';
        $handlers = [
            $currentHandler => $currentHandlerClass,
        ];
        $data = ['data'];

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($configPath, ScopeInterface::SCOPE_STORE)
            ->willReturn($currentHandler);

        $indexerMock = $this->getMockBuilder(\stdClass::class)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentHandlerClass, $data)
            ->willReturn($indexerMock);

        $this->model = new IndexerHandlerFactory(
            $this->objectManagerMock,
            $this->scopeConfigMock,
            $configPath,
            $handlers
        );

        $this->model->create($data);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Indexer handler is not available: current_handler
     */
    public function testCreateWithoutAvailableHandler()
    {
        $configPath = 'config_path';
        $currentHandler = 'current_handler';
        $currentHandlerClass = 'current_handler_class';
        $handlers = [
            $currentHandler => $currentHandlerClass,
        ];
        $data = ['data'];

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($configPath, ScopeInterface::SCOPE_STORE)
            ->willReturn($currentHandler);

        $indexerMock = $this->getMockBuilder(IndexerInterface::class)
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
            $this->scopeConfigMock,
            $configPath,
            $handlers
        );

        $this->model->create($data);
    }
}
