<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
                $this->getMock('Magento\Framework\Event\Config', array(), array(), '', false),
                $this->getMock('Magento\Framework\EventFactory', array(), array(), '', false),
                $this->getMock('Magento\Framework\Event\ObserverFactory', array(), array(), '', false)
            ),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\UrlInterface'),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Cms\Model\PageFactory'),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\StoreManagerInterface'
            ),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\StoreManagerInterface'
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
    public function dispatch($eventName, array $params = array())
    {
        switch ($eventName) {
            case 'cms_controller_router_match_before':
                $params['condition']->setRedirectUrl('http://www.example.com/');
                break;
        }

        return null;
    }
}
