<?php
/**
 * RouterList model test class
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Router;

class DefaultRouterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Router\DefaultRouter
     */
    protected $_model;

    public function testMatch()
    {
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $actionFactory = $this->createMock(\Magento\Framework\App\ActionFactory::class);
        $actionFactory->expects($this->once())->method('create')->with(
            \Magento\Framework\App\Action\Forward::class
        )->willReturn(
            
                $this->getMockForAbstractClass(\Magento\Framework\App\Action\AbstractAction::class, [], '', false)
            
        );
        $noRouteHandler = $this->createMock(\Magento\Framework\App\Router\NoRouteHandler::class);
        $noRouteHandler->expects($this->any())->method('process')->willReturn(true);
        $noRouteHandlerList = $this->createMock(\Magento\Framework\App\Router\NoRouteHandlerList::class);
        $noRouteHandlerList->expects($this->any())->method('getHandlers')->willReturn([$noRouteHandler]);
        $this->_model = $helper->getObject(
            \Magento\Framework\App\Router\DefaultRouter::class,
            [
                'actionFactory' => $actionFactory,
                'noRouteHandlerList' => $noRouteHandlerList
            ]
        );
        $this->assertInstanceOf(\Magento\Framework\App\Action\AbstractAction::class, $this->_model->match($request));
    }
}
