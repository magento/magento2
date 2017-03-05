<?php
/**
 * RouterList model test class
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Router;

class DefaultRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Router\DefaultRouter
     */
    protected $_model;

    public function testMatch()
    {
        $request = $this->getMock(\Magento\Framework\App\RequestInterface::class, [], [], '', false);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $actionFactory = $this->getMock(\Magento\Framework\App\ActionFactory::class, [], [], '', false);
        $actionFactory->expects($this->once())->method('create')->with(
            \Magento\Framework\App\Action\Forward::class
        )->will(
            $this->returnValue(
                $this->getMockForAbstractClass(\Magento\Framework\App\Action\AbstractAction::class, [], '', false)
            )
        );
        $noRouteHandler = $this->getMock(\Magento\Framework\App\Router\NoRouteHandler::class, [], [], '', false);
        $noRouteHandler->expects($this->any())->method('process')->will($this->returnValue(true));
        $noRouteHandlerList = $this->getMock(
            \Magento\Framework\App\Router\NoRouteHandlerList::class,
            [],
            [],
            '',
            false
        );
        $noRouteHandlerList->expects($this->any())->method('getHandlers')->will($this->returnValue([$noRouteHandler]));
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
