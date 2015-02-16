<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Cms\Controller;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Controller\Router
     */
    protected $_model;

    protected function setUp()
    {
        $this->markTestIncomplete('MAGETWO-3393');
        $this->_model = new \Magento\Cms\Controller\Router(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\ActionFactory'),
            new \Magento\Framework\Event\ManagerInterfaceStub(
                $this->getMockForAbstractClass('Magento\Framework\Event\InvokerInterface'),
                $this->getMock('Magento\Framework\Event\Config', [], [], '', false),
                $this->getMock('Magento\Framework\EventFactory', [], [], '', false),
                $this->getMock('Magento\Framework\Event\ObserverFactory', [], [], '', false)
            ),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\UrlInterface'),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Cms\Model\PageFactory'),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Store\Model\StoreManagerInterface'
            ),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Store\Model\StoreManagerInterface'
            )
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testMatch()
    {
        $this->markTestIncomplete('MAGETWO-3393');
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\App\RequestInterface');
        //Open Node
        $request->setPathInfo('parent_node');
        $controller = $this->_model->match($request);
        $this->assertInstanceOf('Magento\Framework\App\Action\Redirect', $controller);
    }
}
/**
 * Event manager stub
 */
namespace Magento\Framework\Event;

class ManagerStub extends Manager
{
    /**
     * Stub dispatch event
     *
     * @param string $eventName
     * @param array $params
     * @return null
     */
    public function dispatch($eventName, array $params = [])
    {
        switch ($eventName) {
            case 'cms_controller_router_match_before':
                $params['condition']->setRedirectUrl('http://www.example.com/');
                break;
        }

        return null;
    }
}
