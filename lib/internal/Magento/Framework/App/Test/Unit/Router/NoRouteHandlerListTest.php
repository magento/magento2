<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Router;

use Magento\Backend\App\Router\NoRouteHandler as BackendNoRouteHandler;
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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $handlersList = [
            'default_handler' => ['class' => NoRouteHandler::class, 'sortOrder' => 100],
            'backend_handler' => ['class' => BackendNoRouteHandler::class, 'sortOrder' => 10],
        ];

        $this->_model = new NoRouteHandlerList($this->_objectManagerMock, $handlersList);
    }

    /**
     * @return void
     */
    public function testGetHandlers(): void
    {
        $backendHandlerMock = $this->createMock(BackendNoRouteHandler::class);
        $defaultHandlerMock = $this->createMock(NoRouteHandler::class);

        $this->_objectManagerMock
            ->method('create')
            ->willReturnCallback(
                function ($arg) use ($backendHandlerMock, $defaultHandlerMock) {
                    if ($arg === BackendNoRouteHandler::class) {
                        return $backendHandlerMock;
                    } elseif ($arg === NoRouteHandler::class) {
                        return $defaultHandlerMock;
                    }
                }
            );

        $expectedResult = ['0' => $backendHandlerMock, '1' => $defaultHandlerMock];

        $this->assertEquals($expectedResult, $this->_model->getHandlers());
    }
}
