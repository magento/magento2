<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Router;

use Magento\Framework\App\Router\NoRouteHandler;
use Magento\Framework\App\Router\NoRouteHandlerList;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class NoRouteHandlerListTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManagerMock;

    /**
     * @var NoRouteHandlerList
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $handlersList = [
            'default_handler' => ['class' => NoRouteHandler::class, 'sortOrder' => 100],
            'backend_handler' => ['class' => \Magento\Backend\App\Router\NoRouteHandler::class, 'sortOrder' => 10],
        ];

        $this->_model = new NoRouteHandlerList($this->_objectManagerMock, $handlersList);
    }

    public function testGetHandlers()
    {
        $backendHandlerMock = $this->createMock(\Magento\Backend\App\Router\NoRouteHandler::class);
        $defaultHandlerMock = $this->createMock(NoRouteHandler::class);

        $this->_objectManagerMock->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            \Magento\Backend\App\Router\NoRouteHandler::class
        )->willReturn(
            $backendHandlerMock
        );

        $this->_objectManagerMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            NoRouteHandler::class
        )->willReturn(
            $defaultHandlerMock
        );

        $expectedResult = ['0' => $backendHandlerMock, '1' => $defaultHandlerMock];

        $this->assertEquals($expectedResult, $this->_model->getHandlers());
    }
}
