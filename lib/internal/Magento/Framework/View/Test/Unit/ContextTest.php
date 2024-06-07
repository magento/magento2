<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for view Context model
 */
namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TranslateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\View\Context;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContextTest extends TestCase
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var State|MockObject
     */
    protected $appState;

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var DesignInterface|MockObject
     */
    protected $design;

    protected function setUp(): void
    {
        $this->markTestSkipped('Testcase needs to be refactored.');
        $this->appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->design = $this->getMockBuilder(DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'appState' => $this->appState,
                'request' => $this->request,
                'design' => $this->design
            ]
        );
    }

    public function testGetCache()
    {
        $this->assertInstanceOf(CacheInterface::class, $this->context->getCache());
    }

    public function testGetDesignPackage()
    {
        $this->assertInstanceOf(DesignInterface::class, $this->context->getDesignPackage());
    }

    public function testGetEventManager()
    {
        $this->assertInstanceOf(ManagerInterface::class, $this->context->getEventManager());
    }

    public function testGetFrontController()
    {
        $this->assertInstanceOf(
            FrontControllerInterface::class,
            $this->context->getFrontController()
        );
    }

    public function testGetLayout()
    {
        $this->assertInstanceOf(LayoutInterface::class, $this->context->getLayout());
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf(Http::class, $this->context->getRequest());
    }

    public function testGetSession()
    {
        $this->assertInstanceOf(
            SessionManagerInterface::class,
            $this->context->getSession()
        );
    }

    public function testGetScopeConfig()
    {
        $this->assertInstanceOf(
            ScopeConfigInterface::class,
            $this->context->getScopeConfig()
        );
    }

    public function testGetTranslator()
    {
        $this->assertInstanceOf(TranslateInterface::class, $this->context->getTranslator());
    }

    public function testGetUrlBuilder()
    {
        $this->assertInstanceOf(UrlInterface::class, $this->context->getUrlBuilder());
    }

    public function testGetViewConfig()
    {
        $this->assertInstanceOf(ConfigInterface::class, $this->context->getViewConfig());
    }

    public function testGetCacheState()
    {
        $this->assertInstanceOf(StateInterface::class, $this->context->getCacheState());
    }

    public function testGetLogger()
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->context->getLogger());
    }

    public function testGetAppState()
    {
        $this->assertInstanceOf(State::class, $this->context->getAppState());
    }

    public function testGetArea()
    {
        $area = 'frontendArea';

        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($area);

        $this->assertEquals($area, $this->context->getArea());
    }

    public function testGetModuleName()
    {
        $moduleName = 'testModuleName';

        $this->request->expects($this->once())
            ->method('getModuleName')
            ->willReturn($moduleName);

        $this->assertEquals($moduleName, $this->context->getModuleName());
    }

    public function testGetFrontName()
    {
        $frontName = 'testFrontName';

        $this->request->expects($this->once())
            ->method('getModuleName')
            ->willReturn($frontName);

        $this->assertEquals($frontName, $this->context->getFrontName());
    }

    public function testGetControllerName()
    {
        $controllerName = 'testControllerName';

        $this->request->expects($this->once())
            ->method('getControllerName')
            ->willReturn($controllerName);

        $this->assertEquals($controllerName, $this->context->getControllerName());
    }

    public function testGetActionName()
    {
        $actionName = 'testActionName';

        $this->request->expects($this->once())
            ->method('getActionName')
            ->willReturn($actionName);

        $this->assertEquals($actionName, $this->context->getActionName());
    }

    public function testGetFullActionName()
    {
        $frontName = 'testFrontName';
        $controllerName = 'testControllerName';
        $actionName = 'testActionName';
        $fullActionName = 'testfrontname_testcontrollername_testactionname';

        $this->request->expects($this->once())
            ->method('getModuleName')
            ->willReturn($frontName);

        $this->request->expects($this->once())
            ->method('getControllerName')
            ->willReturn($controllerName);

        $this->request->expects($this->once())
            ->method('getActionName')
            ->willReturn($actionName);

        $this->assertEquals($fullActionName, $this->context->getFullActionName());
    }

    /**
     * @param string $headerAccept
     * @param string $acceptType
     *
     * @dataProvider getAcceptTypeDataProvider
     */
    public function testGetAcceptType($headerAccept, $acceptType)
    {
        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Accept')
            ->willReturn($headerAccept);

        $this->assertEquals($acceptType, $this->context->getAcceptType());
    }

    /**
     * @return array
     */
    public static function getAcceptTypeDataProvider()
    {
        return [
            ['json', 'json'],
            ['testjson', 'json'],
            ['soap', 'soap'],
            ['testsoap', 'soap'],
            ['text/html', 'html'],
            ['testtext/html', 'html'],
            ['xml', 'xml'],
            ['someElse', 'xml'],
        ];
    }

    public function testGetPost()
    {
        $key = 'getParamName';
        $default = 'defaultGetParamValue';
        $postValue = 'someGetParamValue';

        $this->request->expects($this->once())
            ->method('getPost')
            ->with($key, $default)
            ->willReturn($postValue);

        $this->assertEquals($postValue, $this->context->getPost($key, $default));
    }

    public function testGetQuery()
    {
        $key = 'getParamName';
        $default = 'defaultGetParamValue';
        $queryValue = 'someGetParamValue';

        $this->request->expects($this->once())
            ->method('getPost')
            ->with($key, $default)
            ->willReturn($queryValue);

        $this->assertEquals($queryValue, $this->context->getQuery($key, $default));
    }

    public function testGetParam()
    {
        $key = 'paramName';
        $default = 'defaultParamValue';
        $paramValue = 'someParamValue';

        $this->request->expects($this->once())
            ->method('getParam')
            ->with($key, $default)
            ->willReturn($paramValue);

        $this->assertEquals($paramValue, $this->context->getParam($key, $default));
    }

    public function testGetParams()
    {
        $params = ['paramName' => 'value'];

        $this->request->expects($this->once())
            ->method('getParams')
            ->willReturn($params);

        $this->assertEquals($params, $this->context->getParams());
    }

    public function testGetHeader()
    {
        $headerName = 'headerName';
        $headerValue = 'headerValue';

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with($headerName)
            ->willReturn($headerValue);

        $this->assertEquals($headerValue, $this->context->getHeader($headerName));
    }

    public function testContent()
    {
        $content = 'body string';

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $this->assertEquals($content, $this->context->getContent());
    }
}
