<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\App\Request;
use Magento\Framework\App\ScopeInterface;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface\Proxy | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_routerListMock;

    /**
     * @var \Magento\Framework\App\Request\PathInfoProcessorInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_infoProcessorMock;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieReaderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieReaderMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var array
     */
    private $serverArray;

    protected function setUp()
    {

        $this->_routerListMock = $this->getMock(
            'Magento\Framework\App\Route\ConfigInterface\Proxy',
            ['getRouteFrontName', 'getRouteByFrontName', '__wakeup'],
            [],
            '',
            false
        );
        $this->_infoProcessorMock = $this->getMock('Magento\Framework\App\Request\PathInfoProcessorInterface');
        $this->_infoProcessorMock->expects($this->any())->method('process')->will($this->returnArgument(1));
        $this->cookieReaderMock = $this->getMock('Magento\Framework\Stdlib\Cookie\CookieReaderInterface');
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');

        // Stash the $_SERVER array to protect it from modification in test
        $this->serverArray = $_SERVER;
    }

    public function tearDown()
    {
        $_SERVER = $this->serverArray;
    }

    private function getModel($uri = null)
    {
        return new \Magento\Framework\App\Request\Http(
                $this->_routerListMock,
                $this->_infoProcessorMock,
                $this->cookieReaderMock,
                $this->objectManager,
                $uri
        );
    }

    public function testGetOriginalPathInfoWithTestUri()
    {
        $uri = 'http://test.com/value';
        $this->_model = $this->getModel($uri);
        $this->assertEquals('/value', $this->_model->getOriginalPathInfo());
    }

    public function testGetOriginalPathInfoWithEmptyUri()
    {
        $this->_model = $this->getModel();
        $this->assertEmpty($this->_model->getOriginalPathInfo());
    }

    public function testGetBasePathWithPath()
    {
        $this->_model = $this->getModel();
        $this->_model->setBasePath('http:\/test.com\one/two');
        $this->assertEquals('http://test.com/one/two', $this->_model->getBasePath());
    }

    public function testGetBasePathWithoutPath()
    {
        $this->_model = $this->getModel();
        $this->_model->setBasePath(null);
        $this->assertEquals('/', $this->_model->getBasePath());
    }

    public function testSetRouteNameWithRouter()
    {
        $router = $this->getMock('Magento\Framework\App\Router\AbstractRouter', [], [], '', false);
        $this->_routerListMock->expects($this->any())->method('getRouteFrontName')->will($this->returnValue($router));
        $this->_model = $this->getModel();
        $this->_model->setRouteName('RouterName');
        $this->assertEquals('RouterName', $this->_model->getRouteName());
    }

    public function testSetRouteNameWithNullRouterValue()
    {
        $this->_model = $this->getModel();
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
        $this->_model = $this->getModel();
        $this->_model->setAlias('AliasName', 'AliasTarget');
        $this->assertEquals('AliasTarget', $this->_model->getAlias('AliasName'));
    }

    public function testGetAliasWhenAliasesIsNull()
    {
        $this->_model = $this->getModel();
        $this->assertNull($this->_model->getAlias('someValue'));
    }

    public function testGetRouteNameWithNullValueRouteName()
    {
        $this->_model = $this->getModel();
        $this->_model->setRouteName('RouteName');
        $this->assertEquals('RouteName', $this->_model->getRouteName());
    }

    public function testGetRouteName()
    {
        $this->_model = $this->getModel();
        $expected = 'RouteName';
        $this->_model->setRouteName($expected);
        $this->assertEquals($expected, $this->_model->getRouteName());
    }

    public function testGetFullActionName()
    {
        $this->_model = $this->getModel();
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
        $this->_model = $this->getModel();
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
        $this->_model = $this->getModel();

        $this->assertFalse($this->_model->isAjax());

        $this->_model->clearParams();
        $this->_model->setParam('ajax', 1);
        $this->assertTrue($this->_model->isAjax());

        $this->_model->clearParams();
        $this->_model->setParam('isAjax', 1);
        $this->assertTrue($this->_model->isAjax());

        $this->_model->clearParams();
        $this->_model->getHeaders()->addHeaderLine('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($this->_model->isAjax());

        $this->_model->getHeaders()->clearHeaders();
        $this->_model->getHeaders()->addHeaderLine('X-Requested-With', 'NotXMLHttpRequest');
        $this->assertFalse($this->_model->isAjax());
    }

    public function testSetPostValue()
    {
        $this->_model = $this->getModel();

        $post = ['one' => '111', 'two' => '222'];
        $this->_model->setPostValue($post);
        $this->assertEquals($post, $this->_model->getPost()->toArray());

        $this->_model->setPostValue([]);
        $this->assertEmpty($this->_model->getPost()->toArray());

        $post = ['post_var' => 'post_value'];
        $this->_model->setPostValue($post);
        $this->_model->setPostValue('post_var 2', 'post_value 2');
        $this->assertEquals(
            ['post_var' => 'post_value', 'post_var 2' => 'post_value 2'],
            $this->_model->getPost()->toArray()
        );
    }

    public function testGetFiles()
    {
        $this->_model = $this->getModel();

        $files = ['one' => '111', 'two' => '222'];
        $this->_model->setFiles(new \Zend\Stdlib\Parameters($files));

        $this->assertEquals($files, $this->_model->getFiles()->toArray());

        foreach ($files as $key => $value) {
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
        $this->_model = $this->getModel();
        $this->assertEquals($expectedResult, $this->_model->getDistroBaseUrl());

        $_SERVER = $originalServerValue;
    }

    /**
     * @param string $scriptName
     * @param string $expected
     * @dataProvider getDistroBaseUrlPathDataProvider
     */
    public function testGetDistroBaseUrlPath($scriptName, $expected)
    {
        $this->assertEquals($expected, Http::getDistroBaseUrlPath(['SCRIPT_NAME' => $scriptName]));
    }

    public function getDistroBaseUrlPathDataProvider()
    {
        return [
            [null, '/'],
            ['./index.php', '/'],
            ['.\\index.php', '/'],
            ['/index.php', '/'],
            ['\\index.php', '/'],
            ['subdir/script.php', 'subdir/'],
            ['subdir\\script.php', 'subdir/'],
            ['sub\\dir\\script.php', 'sub/dir/'],
        ];
    }

    public function testGetCookieDefault()
    {
        $key = "cookieName";
        $default = "defaultValue";

        $this->cookieReaderMock
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

        $this->cookieReaderMock
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

        $this->cookieReaderMock
            ->expects($this->once())
            ->method('getCookie')
            ->with($nullKey, $default)
            ->will($this->returnValue($default));

        $this->assertEquals($default, $this->getModel()->getCookie($nullKey, $default));
    }

    public function serverVariablesProvider()
    {
        $returnValue = [];
        $defaultServerData = [
            'SCRIPT_NAME' => 'index.php',
            'HTTP_HOST' => 'sample.host.com',
            'SERVER_PORT' => '80',
            'HTTPS' => '1'
        ];

        $secureUnusualPort = $noHttpsData = $httpsOffData = $noHostData = $noScriptNameData = $defaultServerData;

        unset($noScriptNameData['SCRIPT_NAME']);
        $returnValue['no SCRIPT_NAME'] = [$noScriptNameData, 'http://localhost/'];

        unset($noHostData['HTTP_HOST']);
        $returnValue['no HTTP_HOST'] = [$noHostData, 'http://localhost/'];

        $httpsOffData['HTTPS'] = 'off';
        $returnValue['HTTPS off'] = [$httpsOffData, 'http://sample.host.com/'];

        unset($noHttpsData['HTTPS']);
        $returnValue['no HTTPS'] = [$noHttpsData, 'http://sample.host.com/'];

        $noHttpsNoServerPort = $noHttpsData;
        unset($noHttpsNoServerPort['SERVER_PORT']);
        $returnValue['no SERVER_PORT'] = [$noHttpsNoServerPort, 'http://sample.host.com/'];

        $noHttpsButSecurePort = $noHttpsData;
        $noHttpsButSecurePort['SERVER_PORT'] = 443;
        $returnValue['no HTTP but secure port'] = [$noHttpsButSecurePort, 'https://sample.host.com/'];

        $notSecurePort = $noHttpsData;
        $notSecurePort['SERVER_PORT'] = 81;
        $notSecurePort['HTTP_HOST'] = 'sample.host.com:81';
        $returnValue['not secure not standard port'] = [$notSecurePort, 'http://sample.host.com:81/'];

        $secureUnusualPort['SERVER_PORT'] = 441;
        $secureUnusualPort['HTTP_HOST'] = 'sample.host.com:441';
        $returnValue['not standard secure port'] = [$secureUnusualPort, 'https://sample.host.com:441/'];

        $customUrlPathData = $noHttpsData;
        $customUrlPathData['SCRIPT_FILENAME'] = '/some/dir/custom.php';
        $returnValue['custom path'] = [$customUrlPathData, 'http://sample.host.com/'];

        return $returnValue;
    }

    /**
     * @dataProvider isSecureDataProvider
     *
     * @param bool $isSecure expected output of isSecure method
     * @param string $serverHttps value of $_SERVER['HTTPS']
     * @param string $headerOffloadKey <Name-Of-Offload-Header>
     * @param string $headerOffloadValue value of $_SERVER[<Name-Of-Offload-Header>]
     * @param int $configCall number of times config->getValue is expected to be called
     */
    public function testIsSecure($isSecure, $serverHttps, $headerOffloadKey, $headerOffloadValue, $configCall)
    {
        $this->_model = $this->getModel();
        $configOffloadHeader = 'Header-From-Proxy';
        $configMock = $this->getMockBuilder('Magento\Framework\App\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $configMock->expects($this->exactly($configCall))
            ->method('getValue')
            ->with(\Magento\Framework\App\Request\Http::XML_PATH_OFFLOADER_HEADER, ScopeInterface::SCOPE_DEFAULT)
            ->willReturn($configOffloadHeader);
        $this->objectManager->expects($this->exactly($configCall))
            ->method('get')
            ->with('Magento\Framework\App\Config')
            ->will($this->returnValue($configMock));

        $this->_model->getServer()->set($headerOffloadKey, $headerOffloadValue);
        $this->_model->getServer()->set('HTTPS', $serverHttps);

        $this->assertSame($isSecure, $this->_model->isSecure());
    }

    public function isSecureDataProvider()
    {
        /**
         * Data structure:
         * 'Test #' => [
         *      expected output of isSecure method
         *      value of $_SERVER['HTTPS'],
         *      <Name-Of-Offload-Header>,
         *      value of $_SERVER[<Name-Of-Offload-Header>]
         *      number of times config->getValue is expected to be called
         *  ]
         */
        return [
            'Test 1' => [true, 'on', 'Header-From-Proxy', 'https', 0],
            'Test 2' => [true, 'off', 'Header-From-Proxy', 'https', 1],
            'Test 3' => [true, 'any-string', 'Header-From-Proxy', 'https', 0],
            'Test 4' => [true, 'on', 'Header-From-Proxy', 'http', 0],
            'Test 5' => [false, 'off', 'Header-From-Proxy', 'http', 1],
            'Test 6' => [true, 'any-string', 'Header-From-Proxy', 'http', 0],
            'Test 7' => [true, 'on', 'Header-From-Proxy', 'any-string', 0],
            'Test 8' => [false, 'off', 'Header-From-Proxy', 'any-string', 1],
            'Test 9' => [true, 'any-string', 'Header-From-Proxy', 'any-string', 0],
            'blank HTTPS with proxy set https' => [true, '', 'Header-From-Proxy', 'https', 1],
            'blank HTTPS with proxy set http' => [false, '', 'Header-From-Proxy', 'http', 1],
            'HTTPS off with HTTP_ prefixed proxy set to https' => [true, 'off', 'HTTP_Header-From-Proxy', 'https', 1],
        ];
    }
}
