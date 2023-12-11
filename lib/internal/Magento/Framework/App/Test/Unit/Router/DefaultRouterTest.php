<?php
/**
 * RouterList model test class
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Router;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Router\DefaultRouter;
use Magento\Framework\App\Router\NoRouteHandler;
use Magento\Framework\App\Router\NoRouteHandlerList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class DefaultRouterTest extends TestCase
{
    /**
     * @var DefaultRouter
     */
    protected $_model;

    public function testMatch()
    {
        $request = $this->getMockForAbstractClass(RequestInterface::class);
        $helper = new ObjectManager($this);
        $actionFactory = $this->createMock(ActionFactory::class);
        $actionFactory->expects($this->once())->method('create')->with(
            Forward::class
        )->willReturn(
            $this->getMockForAbstractClass(AbstractAction::class, [], '', false)
        );
        $noRouteHandler = $this->createMock(NoRouteHandler::class);
        $noRouteHandler->expects($this->any())->method('process')->willReturn(true);
        $noRouteHandlerList = $this->createMock(NoRouteHandlerList::class);
        $noRouteHandlerList->expects($this->any())->method('getHandlers')->willReturn([$noRouteHandler]);
        $this->_model = $helper->getObject(
            DefaultRouter::class,
            [
                'actionFactory' => $actionFactory,
                'noRouteHandlerList' => $noRouteHandlerList
            ]
        );
        $this->assertInstanceOf(AbstractAction::class, $this->_model->match($request));
    }
}
