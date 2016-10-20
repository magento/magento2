<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Router;

class NoRouteHandlerListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\App\Router\NoRouteHandlerList
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $handlersList = [
            'default_handler' => ['class' => \Magento\Framework\App\Router\NoRouteHandler::class, 'sortOrder' => 100],
            'backend_handler' => ['class' => \Magento\Backend\App\Router\NoRouteHandler::class, 'sortOrder' => 10],
        ];

        $this->_model = new \Magento\Framework\App\Router\NoRouteHandlerList($this->_objectManagerMock, $handlersList);
    }

    public function testGetHandlers()
    {
        $backendHandlerMock = $this->getMock(\Magento\Backend\App\Router\NoRouteHandler::class, [], [], '', false);
        $defaultHandlerMock = $this->getMock(\Magento\Framework\App\Router\NoRouteHandler::class, [], [], '', false);

        $this->_objectManagerMock->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            \Magento\Backend\App\Router\NoRouteHandler::class
        )->will(
            $this->returnValue($backendHandlerMock)
        );

        $this->_objectManagerMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            \Magento\Framework\App\Router\NoRouteHandler::class
        )->will(
            $this->returnValue($defaultHandlerMock)
        );

        $expectedResult = ['0' => $backendHandlerMock, '1' => $defaultHandlerMock];

        $this->assertEquals($expectedResult, $this->_model->getHandlers());
    }
}
