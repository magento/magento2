<?php
/**
 *
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
namespace Magento\Framework\App\Request;

use Magento\Framework\App\Request\Http as Request;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_routerListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_infoProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cookieManagerMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_routerListMock = $this->getMock('Magento\Framework\App\Route\ConfigInterface');
        $this->_infoProcessorMock = $this->getMock('Magento\Framework\App\Request\PathInfoProcessorInterface');
        $this->_infoProcessorMock->expects($this->any())->method('process')->will($this->returnArgument(1));
        $this->_cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManager');
    }

    public function testGetOriginalPathInfoWithTestUri()
    {
        $uri = 'http://test.com/value';
        $this->_model = $this->getModel($uri);

        $this->assertEquals('/value', $this->_model->getOriginalPathInfo());
    }

    private function getModel($uri = null)
    {
        return $this->_objectManager->getObject(
            'Magento\Framework\App\Request\Http',
            [
                'pathInfoProcessor' => $this->_infoProcessorMock,
                'routeConfig' => $this->_routerListMock,
                'cookieManager' => $this->_cookieManagerMock,
                'uri' => $uri
            ]
        );
    }

    public function testGetOriginalPathInfoWithEmptyUri()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->assertEmpty($this->_model->getOriginalPathInfo());
    }

    public function testSetPathInfoWithNullValue()
    {
        $this->_model = $this->_model = $this->getModel();
        $actual = $this->_model->setPathInfo();
        $this->assertEquals($this->_model, $actual);
    }

    public function testSetPathInfoWithValue()
    {
        $this->_model = $this->_model = $this->getModel();
        $expected = 'testPathInfo';
        $this->_model->setPathInfo($expected);
        $this->assertEquals($expected, $this->_model->getPathInfo());
    }

    public function testSetPathInfoWithQueryPart()
    {

        $uri = 'http://test.com/node?queryValue';
        $this->_model = $this->_model = $this->getModel($uri);
        $this->_model->setPathInfo();
        $this->assertEquals('/node', $this->_model->getPathInfo());
    }

    public function testRewritePathInfoWithNewValue()
    {
        $expected = '/other/path';
        $uri = 'http://test.com/one/two';
        $this->_model = $this->_model = $this->getModel($uri);
        $this->_model->rewritePathInfo($expected);
        $this->assertEquals($expected, $this->_model->getPathInfo());
    }

    public function testRewritePathInfoWithSameValue()
    {
        $expected = '/one/two';

        $uri = 'http://test.com' . $expected;
        $this->_model = $this->_model = $this->getModel($uri);
        $this->_model->rewritePathInfo($expected);
        $this->assertEquals($expected, $this->_model->getPathInfo());
    }

    public function testGetBasePathWithPath()
    {

        $this->_model = $this->_model = $this->getModel();
        $this->_model->setBasePath('http:\/test.com\one/two');
        $this->assertEquals('http://test.com/one/two', $this->_model->getBasePath());
    }

    public function testGetBasePathWithoutPath()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->_model->setBasePath();
        $this->assertEquals('/', $this->_model->getBasePath());
    }

    public function testGetBaseUrlWithUrl()
    {

        $this->_model = $this->_model = $this->getModel();
        $this->_model->setBaseUrl('http:\/test.com\one/two');
        $this->assertEquals('http://test.com/one/two', $this->_model->getBaseUrl());
    }

    public function testGetBaseUrlWithEmptyUrl()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->_model->setBaseUrl();
        $this->assertEmpty($this->_model->getBaseUrl());
    }

    public function testSetRouteNameWithRouter()
    {
        $router = $this->getMock('Magento\Framework\App\Router\AbstractRouter', array(), array(), '', false);
        $this->_routerListMock->expects($this->any())->method('getRouteFrontName')->will($this->returnValue($router));
        $this->_model = $this->_model = $this->getModel();
        $this->_model->setRouteName('RouterName');
        $this->assertEquals('RouterName', $this->_model->getRouteName());
    }

    public function testSetRouteNameWithNullRouterValue()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->_routerListMock->expects($this->once())->method('getRouteFrontName')->will($this->returnValue(null));
        $this->_model->setRouteName('RouterName');
    }

    public function testGetFrontName()
    {

        $uri = 'http://test.com/one/two';
        $this->_model = $this->getModel($uri);
        $this->assertEquals('one', $this->_model->getFrontName());
    }

    public function testGetAliasWhenAliasExists()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->_model->setAlias('AliasName', 'AliasTarget');
        $this->assertEquals('AliasTarget', $this->_model->getAlias('AliasName'));
    }

    public function testGetAliasWhenAliasesIsNull()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->assertNull($this->_model->getAlias('someValue'));
    }

    public function testGetAliasesWhenAliasSet()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->_model->setAlias('AliasName', 'AliasTarget');
        $this->assertEquals(array('AliasName' => 'AliasTarget'), $this->_model->getAliases());
    }

    public function testGetAliasesWhenAliasAreEmpty()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->assertEmpty($this->_model->getAliases());
    }

    public function testGetRequestedRouteNameWhenRequestedRouteIsSet()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->_model->setRoutingInfo(array('requested_route' => 'ExpectedValue'));
        $this->assertEquals('ExpectedValue', $this->_model->getRequestedRouteName());
    }

    public function testGetRequestedRouteNameWithNullValueRouteName()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->_model->setRouteName('RouteName');
        $this->assertEquals('RouteName', $this->_model->getRequestedRouteName());
    }

    public function testGetRequestedRouteNameWithRewritePathInfo()
    {
        $this->_model = $this->_model = $this->getModel();
        $expected = 'TestValue';
        $this->_model->setPathInfo($expected);
        $this->_model->rewritePathInfo($expected . '/other');
        $this->_routerListMock->expects(
            $this->once()
        )->method(
            'getRouteByFrontName'
        )->with(
            $expected
        )->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->_model->getRequestedRouteName());
    }

    public function testGetRequestedRouteNameWithoutRewritePathInfo()
    {
        $this->_model = $this->_model = $this->getModel();
        $expected = 'RouteName';
        $this->_model->setRouteName($expected);
        $this->assertEquals($expected, $this->_model->getRequestedRouteName());
    }

    public function testGetRequestedControllerNameWithRequestedController()
    {
        $this->_model = $this->_model = $this->getModel();
        $expected = array('requested_controller' => 'ControllerName');
        $this->_model->setRoutingInfo($expected);
        $test = $this->_model->getRequestedControllerName();
        $this->assertEquals($expected['requested_controller'], $test);
    }

    public function testGetRequestedControllerNameWithRewritePathInfo()
    {
        $this->_model = $this->_model = $this->getModel();
        $path = 'one/two/';
        $this->_model->setPathInfo($path);
        $this->_model->rewritePathInfo($path . '/last');
        $this->assertEquals('two', $this->_model->getRequestedControllerName());
    }

    public function testGetRequestedActionNameWithRoutingInfo()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->_model->setRoutingInfo(array('requested_action' => 'ExpectedValue'));
        $this->assertEquals('ExpectedValue', $this->_model->getRequestedActionName());
    }

    public function testGetRequestedActionNameWithRewritePathInfo()
    {
        $this->_model = $this->_model = $this->getModel();
        $path = 'one/two/three';
        $this->_model->setPathInfo($path);
        $this->_model->rewritePathInfo($path . '/last');
        $this->assertEquals('three', $this->_model->getRequestedActionName());
    }

    public function testIsStraightWithTrueValue()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->assertTrue($this->_model->isStraight(true));
    }

    public function testIsStraightWithDefaultValue()
    {
        $this->_model = $this->_model = $this->getModel();
        $this->assertFalse($this->_model->isStraight());
    }

    public function testGetFullActionName()
    {
        $this->_model = $this->_model = $this->getModel();
        /* empty request */
        $this->assertEquals('__', $this->_model->getFullActionName());
        $this->_model->setRouteName('test')->setControllerName('controller')->setActionName('action');
        $this->assertEquals('test/controller/action', $this->_model->getFullActionName('/'));
    }

    public function testInitForward()
    {
        $expected = $this->_initForward();
        $this->assertEquals($expected, $this->_model->getBeforeForwardInfo());
    }

    public function testGetBeforeForwardInfo()
    {
        $beforeForwardInfo = $this->_initForward();
        $this->assertNull($this->_model->getBeforeForwardInfo('not_existing_forward_info_key'));
        foreach (array_keys($beforeForwardInfo) as $key) {
            $this->assertEquals($beforeForwardInfo[$key], $this->_model->getBeforeForwardInfo($key));
        }
        $this->assertEquals($beforeForwardInfo, $this->_model->getBeforeForwardInfo());
    }

    /**
     * Initialize $_beforeForwardInfo
     *
     * @return array Contents of $_beforeForwardInfo
     */
    protected function _initForward()
    {
        $this->_model = $this->_model = $this->getModel();
        $beforeForwardInfo = [
            'params' => ['one' => '111', 'two' => '222'],
            'action_name' => 'ActionName',
            'controller_name' => 'ControllerName',
            'module_name' => 'ModuleName',
            'route_name' => 'RouteName'
        ];
        $this->_model->setParams($beforeForwardInfo['params']);
        $this->_model->setActionName($beforeForwardInfo['action_name']);
        $this->_model->setControllerName($beforeForwardInfo['controller_name']);
        $this->_model->setModuleName($beforeForwardInfo['module_name']);
        $this->_model->setRouteName($beforeForwardInfo['route_name']);
        $this->_model->initForward();
        return $beforeForwardInfo;
    }

    public function testIsAjax()
    {
        $this->_model = $this->_model = $this->getModel();

        $this->assertFalse($this->_model->isAjax());

        $this->_model->clearParams();
        $this->_model->setParam('ajax', 1);
        $this->assertTrue($this->_model->isAjax());

        $this->_model->clearParams();
        $this->_model->setParam('isAjax', 1);
        $this->assertTrue($this->_model->isAjax());

        $this->_model->clearParams();
        $server = $_SERVER;
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue($this->_model->isAjax());
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'NotXMLHttpRequest';
        $this->assertFalse($this->_model->isAjax());
        $_SERVER = $server;
    }

    public function testSetPost()
    {
        $this->_model = $this->_model = $this->getModel();

        $post = ['one' => '111', 'two' => '222'];
        $this->_model->setPost($post);
        $this->assertEquals($post, $_POST);

        $this->_model->setPost([]);
        $this->assertEmpty($_POST);

        $_POST = ['post_var' => 'post_value'];
        $this->_model->setPost('post_var 2', 'post_value 2');
        $this->assertEquals(['post_var' => 'post_value', 'post_var 2' => 'post_value 2'], $_POST);
    }

    public function testGetFiles()
    {
        $this->_model = $this->_model = $this->getModel();

        $_FILES = ['one' => '111', 'two' => '222'];
        $this->assertEquals($_FILES, $this->_model->getFiles());

        foreach ($_FILES as $key => $value) {
            $this->assertEquals($value, $this->_model->getFiles($key));
        }

        $this->assertNull($this->_model->getFiles('no_such_file'));

        $this->assertEquals('default', $this->_model->getFiles('no_such_file', 'default'));
    }

    /**
     * @param $serverVariables array
     * @param $expectedResult string
     * @dataProvider serverVariablesProvider
     */
    public function testGetDistroBaseUrl($serverVariables, $expectedResult)
    {
        $originalServerValue = $_SERVER;
        $_SERVER = $serverVariables;
        $this->_model = $this->_model = $this->getModel();
        $this->assertEquals($expectedResult, $this->_model->getDistroBaseUrl());

        $_SERVER = $originalServerValue;
    }

    public function testGetCookieDefault()
    {
        $key = "cookieName";
        $default = "defaultValue";

        $this->_cookieManagerMock
            ->expects($this->once())
            ->method('getCookie')
            ->with($key, $default)
            ->will($this->returnValue($default));

        $this->assertEquals($default, $this->getModel()->getCookie($key, $default));
    }

    public function testGetCookieNameExists()
    {
        $key = "cookieName";
        $default = "defaultValue";
        $value = "cookieValue";

        $this->_cookieManagerMock
            ->expects($this->once())
            ->method('getCookie')
            ->with($key, $default)
            ->will($this->returnValue($value));

        $this->assertEquals($value, $this->getModel()->getCookie($key, $default));
    }

    public function testGetCookieNullName()
    {
        $nullKey = null;
        $default = "defaultValue";

        $this->_cookieManagerMock
            ->expects($this->once())
            ->method('getCookie')
            ->with($nullKey, $default)
            ->will($this->returnValue($default));

        $this->assertEquals($default, $this->getModel()->getCookie($nullKey, $default));
    }

    public function serverVariablesProvider()
    {
        $returnValue = array();
        $defaultServerData = array(
            'SCRIPT_NAME' => 'index.php',
            'HTTP_HOST' => 'sample.host.com',
            'SERVER_PORT' => '80',
            'HTTPS' => '1'
        );

        $secureUnusualPort = $noHttpsData = $httpsOffData = $noHostData = $noScriptNameData = $defaultServerData;

        unset($noScriptNameData['SCRIPT_NAME']);
        $returnValue['no SCRIPT_NAME'] = array($noScriptNameData, 'http://localhost/');

        unset($noHostData['HTTP_HOST']);
        $returnValue['no HTTP_HOST'] = array($noHostData, 'http://localhost/');

        $httpsOffData['HTTPS'] = 'off';
        $returnValue['HTTPS off'] = array($httpsOffData, 'http://sample.host.com/');

        unset($noHttpsData['HTTPS']);
        $returnValue['no HTTPS'] = array($noHttpsData, 'http://sample.host.com/');

        $noHttpsNoServerPort = $noHttpsData;
        unset($noHttpsNoServerPort['SERVER_PORT']);
        $returnValue['no SERVER_PORT'] = array($noHttpsNoServerPort, 'http://sample.host.com/');

        $noHttpsButSecurePort = $noHttpsData;
        $noHttpsButSecurePort['SERVER_PORT'] = 443;
        $returnValue['no HTTP but secure port'] = array($noHttpsButSecurePort, 'https://sample.host.com/');

        $notSecurePort = $noHttpsData;
        $notSecurePort['SERVER_PORT'] = 81;
        $notSecurePort['HTTP_HOST'] = 'sample.host.com:81';
        $returnValue['not secure not standard port'] = array($notSecurePort, 'http://sample.host.com:81/');

        $secureUnusualPort['SERVER_PORT'] = 441;
        $secureUnusualPort['HTTP_HOST'] = 'sample.host.com:441';
        $returnValue['not standard secure port'] = array($secureUnusualPort, 'https://sample.host.com:441/');

        $customUrlPathData = $noHttpsData;
        $customUrlPathData['SCRIPT_FILENAME'] = '/some/dir/custom.php';
        $returnValue['custom path'] = array($customUrlPathData, 'http://sample.host.com/');

        return $returnValue;
    }


}
