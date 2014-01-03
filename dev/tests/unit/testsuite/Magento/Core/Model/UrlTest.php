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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Core\Model\Url
 */
namespace Magento\Core\Model;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Url
     */
    protected $_model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $this->_objectManager->getObject('\Magento\Core\Model\Url');
    }

    public function testSetRoutePath()
    {
        $moduleFrontName = 'moduleFrontName';
        $controllerName = 'controllerName';
        $actionName = 'actionName';

        $this->assertNull($this->_model->getRouteName());
        $this->assertNull($this->_model->getControllerName());
        $this->assertNull($this->_model->getActionName());

        $this->_model->setRoutePath($moduleFrontName . '/' . $controllerName . '/' . $actionName);

        $this->assertNotNull($this->_model->getRouteName());
        $this->assertEquals($moduleFrontName, $this->_model->getRouteName());

        $this->assertNotNull($this->_model->getControllerName());
        $this->assertEquals($controllerName, $this->_model->getControllerName());

        $this->assertNotNull($this->_model->getActionName());
        $this->assertEquals($actionName, $this->_model->getActionName());
    }

    public function testSetRoutePathWhenAsteriskUses()
    {
        $moduleFrontName = 'moduleFrontName';
        $controllerName = 'controllerName';
        $actionName = 'actionName';

        $requestMock = $this->getMockForAbstractClass(
            'Magento\App\Request\Http',
            array(),
            '',
            false,
            false,
            true,
            array('getRequestedRouteName', 'getRequestedControllerName', 'getRequestedActionName')
        );

        $requestMock->expects($this->once())->method('getRequestedRouteName')
            ->will($this->returnValue($moduleFrontName));
        $requestMock->expects($this->once())->method('getRequestedControllerName')
            ->will($this->returnValue($controllerName));
        $requestMock->expects($this->once())->method('getRequestedActionName')
            ->will($this->returnValue($actionName));

        $this->_model->setRequest($requestMock);

        $this->_model->setRoutePath('*/*/*');

        $this->assertEquals($moduleFrontName, $this->_model->getRouteName());
        $this->assertEquals($controllerName, $this->_model->getControllerName());
        $this->assertEquals($actionName, $this->_model->getActionName());
    }

    public function testSetRoutePathWhenRouteParamsExists()
    {
        $this->assertNull($this->_model->getControllerName());
        $this->assertNull($this->_model->getActionName());

        $this->_model->setRoutePath('m/c/a/p1/v1/p2/v2');

        $this->assertNotNull($this->_model->getControllerName());
        $this->assertNotNull($this->_model->getActionName());
        $this->assertNotEmpty($this->_model->getRouteParams());

        $this->assertArrayHasKey('p1', $this->_model->getRouteParams());
        $this->assertArrayHasKey('p2', $this->_model->getRouteParams());

        $this->assertEquals('v1', $this->_model->getRouteParam('p1'));
        $this->assertEquals('v2', $this->_model->getRouteParam('p2'));
    }

    /**
     * @param $port mixed
     * @param $url string
     * @dataProvider getCurrentUrlProvider
     */
    public function testGetCurrentUrl($port, $url)
    {
        $methods = array('getServer', 'getScheme', 'getHttpHost', 'getModuleName', 'setModuleName',
            'getActionName', 'setActionName', 'getParam');
        $requestMock = $this->getMock('\Magento\App\RequestInterface', $methods);
        $requestMock->expects($this->at(0))->method('getServer')->with('SERVER_PORT')
            ->will($this->returnValue($port));
        $requestMock->expects($this->at(1))->method('getServer')->with('REQUEST_URI')
            ->will($this->returnValue('/fancy_uri'));
        $requestMock->expects($this->once())->method('getScheme')->will($this->returnValue('http'));
        $requestMock->expects($this->once())->method('getHttpHost')->will($this->returnValue('example.com'));

        /** @var \Magento\Core\Model\Url $model */
        $model = $this->_objectManager->getObject('Magento\Core\Model\Url', array('request' => $requestMock));
        $this->assertEquals($url, $model->getCurrentUrl());
    }

    public function getCurrentUrlProvider()
    {
        return array(
            'without_port' => array('', 'http://example.com/fancy_uri'),
            'default_port' => array(80, 'http://example.com/fancy_uri'),
            'custom_port' => array(8080, 'http://example.com:8080/fancy_uri')
        );
    }
}
