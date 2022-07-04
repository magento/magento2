<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Request;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\PathInfo;
use Magento\Framework\App\Request\PathInfoProcessorInterface;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Route\ConfigInterface\Proxy;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class HttpTest extends TestCase
{
    /**
     * @var Http
     */
    private $model;

    /**
     * @var Proxy|MockObject
     */
    private $routerListMock;

    /**
     * @var PathInfoProcessorInterface|MockObject
     */
    private $infoProcessorMock;

    /**
     * @var PathInfo
     */
    private $pathInfo;

    /**
     * @var ObjectManager|MockObject
     */
    private $objectManagerMock;

    /**
     * @var StringUtils|MockObject
     */
    private $converterMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var array
     */
    private $serverArray;

    protected function setUp(): void
    {
        $this->routerListMock = $this->createPartialMock(
            Proxy::class,
            ['getRouteFrontName', 'getRouteByFrontName', '__wakeup']
        );
        $this->infoProcessorMock = $this->getMockForAbstractClass(PathInfoProcessorInterface::class);
        $this->infoProcessorMock->expects($this->any())->method('process')->willReturnArgument(1);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->converterMock = $this->getMockBuilder(StringUtils::class)
            ->disableOriginalConstructor()
            ->setMethods(['cleanString'])
            ->getMock();

        // Stash the $_SERVER array to protect it from modification in test
        $this->serverArray = $_SERVER;

        $this->objectManager = new ObjectManager($this);
        $this->pathInfo =  $this->objectManager->getObject(PathInfo::class);
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverArray;
    }

    /**
     * @return Http
     */
    private function getModel($uri = null, $appConfigMock = true)
    {
        $model = $this->objectManager->getObject(
            Http::class,
            [
                'routeConfig' => $this->routerListMock,
                'pathInfoProcessor' => $this->infoProcessorMock,
                'pathInfoService' => $this->pathInfo,
                'objectManager' => $this->objectManagerMock,
                'converter' => $this->converterMock,
                'uri' => $uri,
            ]
        );

        if ($appConfigMock) {
            $configMock = $this->createMock(Config::class);
            $this->objectManager->setBackwardCompatibleProperty($model, 'appConfig', $configMock);
        }

        return $model;
    }

    public function testGetOriginalPathInfoWithTestUri()
    {
        $uri = 'http://test.com/value?key=value';
        $this->model = $this->getModel($uri);
        $this->assertEquals('/value', $this->model->getOriginalPathInfo());
    }

    public function testGetOriginalPathInfoWithEmptyUri()
    {
        $this->model = $this->getModel();
        $this->assertEmpty($this->model->getOriginalPathInfo());
    }

    public function testGetBasePathWithPath()
    {
        $this->model = $this->getModel();
        $this->model->setBasePath('http:\/test.com\one/two');
        $this->assertEquals('http://test.com/one/two', $this->model->getBasePath());
    }

    public function testGetBasePathWithoutPath()
    {
        $this->model = $this->getModel();
        $this->model->setBasePath('');
        $this->assertEquals('/', $this->model->getBasePath());
    }

    public function testSetRouteNameWithRouter()
    {
        $router = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->routerListMock->expects($this->any())->method('getRouteFrontName')->willReturn($router);
        $this->model = $this->getModel();
        $this->model->setRouteName('RouterName');
        $this->assertEquals('RouterName', $this->model->getRouteName());
    }

    public function testSetRouteNameWithNullRouterValue()
    {
        $this->model = $this->getModel();
        $this->routerListMock->expects($this->once())->method('getRouteFrontName')->willReturn(null);
        $this->model->setRouteName('RouterName');
    }

    public function testGetFrontName()
    {
        $uri = 'http://test.com/one/two';
        $this->model = $this->getModel($uri);
        $this->assertEquals('one', $this->model->getFrontName());
    }

    public function testGetRouteNameWithNullValueRouteName()
    {
        $this->model = $this->getModel();
        $this->model->setRouteName('RouteName');
        $this->assertEquals('RouteName', $this->model->getRouteName());
    }

    public function testGetRouteName()
    {
        $this->model = $this->getModel();
        $expected = 'RouteName';
        $this->model->setRouteName($expected);
        $this->assertEquals($expected, $this->model->getRouteName());
    }

    public function testGetFullActionName()
    {
        $this->model = $this->getModel();
        /* empty request */
        $this->assertEquals('__', $this->model->getFullActionName());
        $this->model->setRouteName('test')->setControllerName('controller')->setActionName('action');
        $this->assertEquals('test/controller/action', $this->model->getFullActionName('/'));
    }

    public function testInitForward()
    {
        $expected = $this->initForward();
        $this->assertEquals($expected, $this->model->getBeforeForwardInfo());
    }

    public function testGetBeforeForwardInfo()
    {
        $beforeForwardInfo = $this->initForward();
        $this->assertNull($this->model->getBeforeForwardInfo('not_existing_forward_info_key'));
        foreach (array_keys($beforeForwardInfo) as $key) {
            $this->assertEquals($beforeForwardInfo[$key], $this->model->getBeforeForwardInfo($key));
        }
        $this->assertEquals($beforeForwardInfo, $this->model->getBeforeForwardInfo());
    }

    /**
     * Initialize $_beforeForwardInfo
     *
     * @return array Contents of $_beforeForwardInfo
     */
    private function initForward()
    {
        $this->model = $this->getModel();
        $beforeForwardInfo = [
            'params' => ['one' => '111', 'two' => '222'],
            'action_name' => 'ActionName',
            'controller_name' => 'ControllerName',
            'module_name' => 'ModuleName',
            'route_name' => 'RouteName'
        ];
        $this->model->setParams($beforeForwardInfo['params']);
        $this->model->setActionName($beforeForwardInfo['action_name']);
        $this->model->setControllerName($beforeForwardInfo['controller_name']);
        $this->model->setModuleName($beforeForwardInfo['module_name']);
        $this->model->setRouteName($beforeForwardInfo['route_name']);
        $this->model->initForward();
        return $beforeForwardInfo;
    }

    public function testIsAjax()
    {
        $this->model = $this->getModel();

        $this->assertFalse($this->model->isAjax());

        $this->model->clearParams();
        $this->model->setParam('ajax', 1);
        $this->assertTrue($this->model->isAjax());

        $this->model->clearParams();
        $this->model->setParam('isAjax', 1);
        $this->assertTrue($this->model->isAjax());

        $this->model->clearParams();
        $this->model->getHeaders()->addHeaderLine('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($this->model->isAjax());

        $this->model->getHeaders()->clearHeaders();
        $this->model->getHeaders()->addHeaderLine('X-Requested-With', 'NotXMLHttpRequest');
        $this->assertFalse($this->model->isAjax());
    }

    /**
     * @param array $serverVariables
     * @param string $cleanHeaderHttpHost
     * @param string $expectedResult
     * @dataProvider serverVariablesProvider
     */
    public function testGetDistroBaseUrl($serverVariables, $cleanHeaderHttpHost, $expectedResult)
    {
        $originalServerValue = $_SERVER;
        $_SERVER = $serverVariables;
        $this->model = $this->getModel();
        $this->converterMock->expects($this->once())->method('cleanString')->willReturn($cleanHeaderHttpHost);
        $this->assertEquals($expectedResult, $this->model->getDistroBaseUrl());

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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
        $returnValue['no SCRIPT_NAME'] = [$noScriptNameData, $noScriptNameData['HTTP_HOST'], 'http://localhost/'];

        unset($noHostData['HTTP_HOST']);
        $returnValue['no HTTP_HOST'] = [$noHostData, '', 'http://localhost/'];

        $httpsOffData['HTTPS'] = 'off';
        $returnValue['HTTPS off'] = [$httpsOffData, $httpsOffData['HTTP_HOST'], 'http://sample.host.com/'];

        unset($noHttpsData['HTTPS']);
        $returnValue['no HTTPS'] = [$noHttpsData, $noHttpsData['HTTP_HOST'], 'http://sample.host.com/'];

        $noHttpsNoServerPort = $noHttpsData;
        unset($noHttpsNoServerPort['SERVER_PORT']);
        $returnValue['no SERVER_PORT'] = [
            $noHttpsNoServerPort,
            $noHttpsNoServerPort['HTTP_HOST'],
            'http://sample.host.com/'
        ];

        $noHttpsButSecurePort = $noHttpsData;
        $noHttpsButSecurePort['SERVER_PORT'] = 443;
        $returnValue['no HTTP but secure port'] = [
            $noHttpsButSecurePort,
            $noHttpsButSecurePort['HTTP_HOST'],
            'https://sample.host.com/'
        ];

        $notSecurePort = $noHttpsData;
        $notSecurePort['SERVER_PORT'] = 81;
        $notSecurePort['HTTP_HOST'] = 'sample.host.com:81';
        $returnValue['not secure not standard port'] = [
            $notSecurePort,
            $notSecurePort['HTTP_HOST'],
            'http://sample.host.com:81/'
        ];

        $secureUnusualPort['SERVER_PORT'] = 441;
        $secureUnusualPort['HTTP_HOST'] = 'sample.host.com:441';
        $returnValue['not standard secure port'] = [
            $secureUnusualPort,
            $secureUnusualPort['HTTP_HOST'],
            'https://sample.host.com:441/'
        ];

        $customUrlPathData = $noHttpsData;
        $customUrlPathData['SCRIPT_FILENAME'] = '/some/dir/custom.php';
        $returnValue['custom path'] = [$customUrlPathData, $customUrlPathData['HTTP_HOST'], 'http://sample.host.com/'];

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
        $this->model = $this->getModel(null, false);
        $configOffloadHeader = 'Header-From-Proxy';
        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $configMock->expects($this->exactly($configCall))
            ->method('getValue')
            ->with(
                Http::XML_PATH_OFFLOADER_HEADER,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            )->willReturn($configOffloadHeader);

        $this->objectManager->setBackwardCompatibleProperty($this->model, 'appConfig', $configMock);
        $this->objectManager->setBackwardCompatibleProperty($this->model, 'sslOffloadHeader', null);

        $this->model->getServer()->set($headerOffloadKey, $headerOffloadValue);
        $this->model->getServer()->set('HTTPS', $serverHttps);

        $this->assertSame($isSecure, $this->model->isSecure());
    }

    /**
     * @dataProvider httpSafeMethodProvider
     * @backupGlobals enabled
     * @param string $httpMethod value of $_SERVER['REQUEST_METHOD']
     */
    public function testIsSafeMethodTrue($httpMethod)
    {
        $this->model = $this->getModel();
        $_SERVER['REQUEST_METHOD'] = $httpMethod;
        $this->assertTrue($this->model->isSafeMethod());
    }

    /**
     * @dataProvider httpNotSafeMethodProvider
     * @backupGlobals enabled
     * @param string $httpMethod value of $_SERVER['REQUEST_METHOD']
     */
    public function testIsSafeMethodFalse($httpMethod)
    {
        $this->model = $this->getModel();
        $_SERVER['REQUEST_METHOD'] = $httpMethod;
        $this->assertFalse($this->model->isSafeMethod());
    }

    /**
     * @return array
     */
    public function httpSafeMethodProvider()
    {
        return [
            'Test 1' => ['GET'],
            'Test 2' => ['HEAD'],
            'Test 3' => ['TRACE'],
            'Test 4' => ['OPTIONS']
        ];
    }

    /**
     * @return array
     */
    public function httpNotSafeMethodProvider()
    {
        return [
            'Test 1' => ['POST'],
            'Test 2' => ['PUT'],
            'Test 3' => ['DELETE'],
            'Test 4' => ['PATCH'],
            'Test 5' => ['CONNECT'],
            'Test 6' => [null]
        ];
    }

    /**
     * @return array
     */
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
            'Test 1' => [true, 'on', 'HEADER_FROM_PROXY', 'https', 0],
            'Test 2' => [true, 'off', 'HEADER_FROM_PROXY', 'https', 1],
            'Test 3' => [true, 'any-string', 'HEADER_FROM_PROXY', 'https', 0],
            'Test 4' => [true, 'on', 'HEADER_FROM_PROXY', 'http', 0],
            'Test 5' => [false, 'off', 'HEADER_FROM_PROXY', 'http', 1],
            'Test 6' => [true, 'any-string', 'HEADER_FROM_PROXY', 'http', 0],
            'Test 7' => [true, 'on', 'HEADER_FROM_PROXY', 'any-string', 0],
            'Test 8' => [false, 'off', 'HEADER_FROM_PROXY', 'any-string', 1],
            'Test 9' => [true, 'any-string', 'HEADER_FROM_PROXY', 'any-string', 0],
            'blank HTTPS with proxy set https' => [true, '', 'HEADER_FROM_PROXY', 'https', 1],
            'blank HTTPS with proxy set http' => [false, '', 'HEADER_FROM_PROXY', 'http', 1],
            'HTTPS off with HTTP_ prefixed proxy set to https' => [true, 'off', 'HTTP_HEADER_FROM_PROXY', 'https', 1],
        ];
    }

    /**
     * @dataProvider setPathInfoDataProvider
     * @param string $requestUri
     * @param string $basePath$
     * @param string $expected
     */
    public function testGetPathInfo($requestUri, $basePath, $expected)
    {
        $this->model = $this->getModel($requestUri);
        $this->model->setBaseUrl($basePath);
        $this->assertEquals($expected, $this->model->getPathInfo());
        $this->assertEquals($expected, $this->model->getOriginalPathInfo());
    }

    public function testSetPathInfo()
    {
        $requestUri = 'http://svr.com//module/route/mypage/myproduct?param1=1';
        $basePath = '/module/route/';
        $this->model = $this->getModel($requestUri);
        $this->model->setBaseUrl($basePath);
        $expected = '/mypage/myproduct';
        $this->assertEquals($expected, $this->model->getOriginalPathInfo());
        $this->model->setPathInfo('http://svr.com/something/route?param1=1');
        $this->assertEquals('http://svr.com/something/route?param1=1', $this->model->getPathInfo());
        $this->assertEquals($expected, $this->model->getOriginalPathInfo());
    }

    /**
     * @return array
     */
    public function setPathInfoDataProvider()
    {
        return [
            ['http://svr.com/', '', ''],
            ['http://svr.com', '', ''],
            ['http://svr.com?param1=1', '', ''],
            ['http://svr.com/?param1=1', '', '/'],
            ['http://svr.com?param1=1&param2=2', '', ''],
            ['http://svr.com/?param1=1&param2=2', '', '/'],
            ['http://svr.com/module', '', '/module'],
            ['http://svr.com/module/', '', '/module/'],
            ['http://svr.com/module/route', '', '/module/route'],
            ['http://svr.com/module/route/', '', '/module/route/'],
            ['http://svr.com/index.php', '/index.php', ''],
            ['http://svr.com/index.php/', '/index.php', '/'],
            ['http://svr.com/index.phpmodule', '/index.php', 'noroute'],
            ['http://svr.com/index.phpmodule/contact', '/index.php/', 'noroute'],
            ['http://svr.com//index.phpmodule/contact', 'index.php', 'noroute'],
            ['http://svr.com/index.phpmodule/contact/', '/index.php/', 'noroute'],
            ['http://svr.com//index.phpmodule/contact/', 'index.php', 'noroute'],
        ];
    }
}
