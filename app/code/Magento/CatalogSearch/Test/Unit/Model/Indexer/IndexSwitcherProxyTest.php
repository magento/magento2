<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\IndexSwitcherInterface;
use Magento\CatalogSearch\Model\Indexer\IndexSwitcherProxy;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;

class IndexSwitcherProxyTest extends \PHPUnit\Framework\TestCase
{
    /** @var IndexSwitcherProxy */
    private $model;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    /** @var EngineResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $engineResolverMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->engineResolverMock = $this->getMockBuilder(EngineResolverInterface::class)
            ->getMockForAbstractClass();
    }

    public function testSwitchIndex()
    {
        $currentHandler = 'current_handler';
        $currentHandlerClass = IndexSwitcherInterface::class;
        $handles = [
            $currentHandler => $currentHandlerClass,
        ];
        $dimensions = ['dimension'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentHandler);

        $indexSwitcherMock = $this->getMockBuilder($currentHandlerClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentHandlerClass)
            ->willReturn($indexSwitcherMock);

        $indexSwitcherMock->expects($this->once())
            ->method('switchIndex')
            ->with($dimensions);

        $this->model = new IndexSwitcherProxy(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $handles
        );

        $this->model->switchIndex($dimensions);
    }

    public function testSwitchIndexWithoutHandlers()
    {
        $currentHandler = 'current_handler';
        $handles = [];
        $dimensions = ['dimension'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentHandler);

        $this->objectManagerMock->expects($this->never())
            ->method('create');

        $this->model = new IndexSwitcherProxy(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $handles
        );

        $this->model->switchIndex($dimensions);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage current_handler index switcher doesn't implement
     */
    public function testSwitchIndexWithWrongHandler()
    {
        $currentHandler = 'current_handler';
        $currentHandlerClass = \stdClass::class;
        $handles = [
            $currentHandler => $currentHandlerClass,
        ];
        $dimensions = ['dimension'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentHandler);

        $indexSwitcherMock = $this->getMockBuilder($currentHandlerClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentHandlerClass)
            ->willReturn($indexSwitcherMock);

        $this->model = new IndexSwitcherProxy(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $handles
        );

        $this->model->switchIndex($dimensions);
    }
}
