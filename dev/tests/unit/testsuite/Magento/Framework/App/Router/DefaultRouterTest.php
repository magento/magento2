<?php
/**
 * RouterList model test class
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Router;

class DefaultRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Router\DefaultRouter
     */
    protected $_model;

    public function testMatch()
    {
        $request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $actionFactory = $this->getMock('Magento\Framework\App\ActionFactory', [], [], '', false);
        $actionFactory->expects($this->once())->method('create')->with(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        )->will(
            $this->returnValue(
                $this->getMockForAbstractClass('Magento\Framework\App\Action\AbstractAction', [], '', false)
            )
        );
        $noRouteHandler = $this->getMock('Magento\Core\App\Router\NoRouteHandler', [], [], '', false);
        $noRouteHandler->expects($this->any())->method('process')->will($this->returnValue(true));
        $noRouteHandlerList = $this->getMock('Magento\Framework\App\Router\NoRouteHandlerList', [], [], '', false);
        $noRouteHandlerList->expects($this->any())->method('getHandlers')->will($this->returnValue([$noRouteHandler]));
        $this->_model = $helper->getObject(
            'Magento\Framework\App\Router\DefaultRouter',
            [
                'actionFactory' => $actionFactory,
                'noRouteHandlerList' => $noRouteHandlerList
            ]
        );
        $this->assertInstanceOf('Magento\Framework\App\Action\AbstractAction', $this->_model->match($request));
    }
}
