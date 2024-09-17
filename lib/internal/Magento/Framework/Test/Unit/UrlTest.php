<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url;
use Magento\Framework\Url\HostChecker;
use Magento\Framework\Url\ModifierInterface;
use Magento\Framework\Url\QueryParamsResolverInterface;
use Magento\Framework\Url\RouteParamsPreprocessorInterface;
use Magento\Framework\Url\RouteParamsResolver;
use Magento\Framework\Url\RouteParamsResolverFactory;
use Magento\Framework\Url\ScopeInterface;
use Magento\Framework\Url\ScopeResolverInterface;
use Magento\Framework\Url\SecurityInfoInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\ZendEscaper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test class for Magento\Framework\Url
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlTest extends TestCase
{
    /**
     * @var RouteParamsResolver|MockObject
     */
    protected $routeParamsResolverMock;

    /**
     * @var RouteParamsPreprocessorInterface|MockObject
     */
    protected $routeParamsPreprocessorMock;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    protected $scopeResolverMock;

    /**
     * @var ScopeInterface|MockObject
     */
    protected $scopeMock;

    /**
     * @var QueryParamsResolverInterface|MockObject
     */
    protected $queryParamsResolverMock;

    /**
     * @var SidResolverInterface|MockObject
     */
    protected $sidResolverMock;

    /**
     * @var Generic|MockObject
     */
    protected $sessionMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var ModifierInterface|MockObject
     */
    protected $urlModifier;

    /**
     * @var HostChecker|MockObject
     */
    private $hostChecker;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->routeParamsResolverMock = $this->getMockBuilder(RouteParamsResolver::class)
            ->addMethods(['getType'])
            ->onlyMethods(['hasData', 'getData', 'getRouteParams', 'unsetData'])
            ->disableOriginalConstructor()
            ->getMock();

        $escaperMock = $this->createMock(ZendEscaper::class);

        $objectManager = new ObjectManager($this);

        $objectManager->setBackwardCompatibleProperty($this->routeParamsResolverMock, 'escaper', $escaperMock);

        $this->routeParamsPreprocessorMock = $this->getMockForAbstractClass(
            RouteParamsPreprocessorInterface::class,
            ['unsetData'],
            '',
            false,
            true,
            true,
            []
        );

        $this->scopeResolverMock = $this->getMockForAbstractClass(ScopeResolverInterface::class);
        $this->scopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->queryParamsResolverMock = $this->getMockForAbstractClass(QueryParamsResolverInterface::class);
        $this->sidResolverMock = $this->getMockForAbstractClass(SidResolverInterface::class);
        $this->sessionMock = $this->createMock(Generic::class);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->urlModifier = $this->getMockForAbstractClass(ModifierInterface::class);
        $this->urlModifier->expects($this->any())
            ->method('execute')
            ->willReturnArgument(0);
    }

    /**
     * @param bool $resolve
     * @return RouteParamsResolverFactory|MockObject
     */
    protected function getRouteParamsResolverFactory($resolve = true): RouteParamsResolverFactory
    {
        $routeParamsResolverFactoryMock = $this->createMock(RouteParamsResolverFactory::class);
        if ($resolve) {
            $routeParamsResolverFactoryMock->expects($this->any())->method('create')
                ->willReturn($this->routeParamsResolverMock);
        }

        return $routeParamsResolverFactoryMock;
    }

    /**
     * @return Http|MockObject
     */
    protected function getRequestMock(): Http
    {
        return $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $arguments
     * @return object
     */
    protected function getUrlModel($arguments = [])
    {
        $arguments = array_merge($arguments, ['scopeType' => StoreScopeInterface::SCOPE_STORE]);
        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(Url::class, $arguments);

        $modelProperty = (new ReflectionClass(get_class($model)))
            ->getProperty('urlModifier');

        $modelProperty->setAccessible(true);
        $modelProperty->setValue($model, $this->urlModifier);

        $zendEscaper = new ZendEscaper();
        $escaper = new Escaper();
        $objectManager->setBackwardCompatibleProperty($escaper, 'escaper', $zendEscaper);
        $objectManager->setBackwardCompatibleProperty($model, 'escaper', $escaper);

        return $model;
    }

    /**
     * @param string $httpHost
     * @param string $url
     *
     * @return void
     * @dataProvider getCurrentUrlProvider
     */
    public function testGetCurrentUrl($httpHost, $url): void
    {
        $requestMock = $this->getRequestMock();
        $requestMock->expects($this->once())->method('getRequestUri')->willReturn('/fancy_uri');
        $requestMock->expects($this->once())->method('getScheme')->willReturn('http');
        $requestMock->expects($this->once())->method('getHttpHost')->willReturn($httpHost);
        $model = $this->getUrlModel(['request' => $requestMock]);
        $this->assertEquals($url, $model->getCurrentUrl());
    }

    /**
     * @return array
     */
    public static function getCurrentUrlProvider(): array
    {
        return [
            'without_port' => ['example.com', 'http://example.com/fancy_uri'],
            'default_port' => ['example.com:80', 'http://example.com/fancy_uri'],
            'custom_port' => ['example.com:8080', 'http://example.com:8080/fancy_uri']
        ];
    }

    /**
     * @return void
     */
    public function testGetBaseUrlNotLinkType(): void
    {
        $model = $this->getUrlModel(
            [
                'scopeResolver' => $this->scopeResolverMock,
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory()
            ]
        );

        $baseUrl = 'base-url';
        $urlType = 'not-link';
        $this->routeParamsResolverMock->expects($this->any())->method('getType')->willReturn($urlType);
        $this->scopeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);

        $baseUrlParams = ['_scope' => $this->scopeMock, '_type' => $urlType, '_secure' => true];
        $this->assertEquals($baseUrl, $model->getBaseUrl($baseUrlParams));
    }

    /**
     * @return void
     */
    public function testGetUrlValidateFilter(): void
    {
        $model = $this->getUrlModel();
        $this->assertEquals('http://test.com', $model->getUrl('http://test.com'));
    }

    /**
     * @param string|array|bool $query
     * @param string $queryResult
     * @param string $returnUri
     *
     * @return void
     * @dataProvider getUrlDataProvider
     */
    public function testGetUrl($query, $queryResult, $returnUri): void
    {
        $requestMock = $this->getRequestMock();
        $routeConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $model = $this->getUrlModel(
            [
                'scopeResolver' => $this->scopeResolverMock,
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
                'queryParamsResolver' => $this->queryParamsResolverMock,
                'request' => $requestMock,
                'routeConfig' => $routeConfigMock,
                'routeParamsPreprocessor' => $this->routeParamsPreprocessorMock
            ]
        );

        $baseUrl = 'http://localhost/index.php/';
        $urlType = UrlInterface::URL_TYPE_LINK;

        $this->scopeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);
        $this->routeParamsResolverMock->expects($this->any())->method('getType')->willReturn($urlType);
        $this->routeParamsResolverMock->expects($this->any())->method('getRouteParams')
            ->willReturn(['id' => 100]);

        $this->routeParamsPreprocessorMock->expects($this->once())
            ->method('execute')
            ->willReturnArgument(2);

        $requestMock->expects($this->once())->method('isDirectAccessFrontendName')->willReturn(true);
        $routeConfigMock->expects($this->once())->method('getRouteFrontName')->willReturn('catalog');
        $this->queryParamsResolverMock->expects($this->once())->method('getQuery')
            ->willReturn($queryResult);

        $url = $model->getUrl('catalog/product/view', [
            '_scope' => $this->getMockForAbstractClass(StoreInterface::class),
            '_fragment' => 'anchor',
            '_escape' => 1,
            '_query' => $query,
            '_nosid' => 0,
            'id' => 100
        ]);
        $this->assertEquals($returnUri, $url);
    }

    /**
     * @return void
     */
    public function testGetUrlIdempotentSetRoutePath(): void
    {
        $model = $this->getUrlModel([
            'scopeResolver' => $this->scopeResolverMock,
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
        ]);
        $model->setData('route_path', 'catalog/product/view');

        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);

        $this->urlModifier->expects($this->exactly(1))->method('execute');

        $this->assertEquals('catalog/product/view', $model->getUrl('catalog/product/view'));
    }

    /**
     * @return void
     */
    public function testGetUrlIdempotentSetRouteName(): void
    {
        $model = $this->getUrlModel([
            'scopeResolver' => $this->scopeResolverMock,
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
            'request' => $this->getRequestMock()
        ]);
        $model->setData('route_name', 'catalog');

        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);

        $this->assertEquals('/product/view/', $model->getUrl('catalog/product/view'));
    }

    /**
     * @return void
     */
    public function testGetUrlRouteHasParams(): void
    {
        $this->routeParamsResolverMock->expects($this->any())->method('getRouteParams')
            ->willReturn(['foo' => 'bar', 'true' => false]);
        $model = $this->getUrlModel([
            'scopeResolver' => $this->scopeResolverMock,
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
            'request' => $this->getRequestMock()
        ]);

        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);

        $this->assertEquals('/index/index/foo/bar/', $model->getUrl('catalog'));
    }

    /**
     * @return void
     */
    public function testGetUrlRouteUseRewrite(): void
    {
        $this->routeParamsResolverMock->expects($this->any())->method('getRouteParams')
            ->willReturn(['foo' => 'bar']);

        $this->routeParamsPreprocessorMock->expects($this->once())
            ->method('execute')
            ->willReturnArgument(2);

        $request = $this->getRequestMock();
        $request->expects($this->once())->method('getAlias')->willReturn('/catalog/product/view/');
        $model = $this->getUrlModel([
            'scopeResolver' => $this->scopeResolverMock,
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
            'request' => $request,
            'routeParamsPreprocessor' => $this->routeParamsPreprocessorMock
        ]);

        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);

        $this->assertEquals('/catalog/product/view/', $model->getUrl('catalog', ['_use_rewrite' => 1]));
    }

    /**
     * @return array
     */
    public static function getUrlDataProvider(): array
    {
        return [
            'string query' => [
                'foo=bar',
                'foo=bar',
                'http://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor'
            ],
            'array query' => [
                ['foo' => 'bar'],
                'foo=bar',
                'http://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor'
            ],
            'without query' => [
                false,
                '',
                'http://localhost/index.php/catalog/product/view/id/100/#anchor'
            ]
        ];
    }

    /**
     * @return void
     */
    public function testGetUrlWithAsterisksPath(): void
    {
        $requestMock = $this->getRequestMock();
        $routeConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $model = $this->getUrlModel(
            [
                'scopeResolver' => $this->scopeResolverMock,
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
                'queryParamsResolver' => $this->queryParamsResolverMock,
                'request' => $requestMock, 'routeConfig' => $routeConfigMock
            ]
        );

        $baseUrl = 'http://localhost/index.php/';
        $urlType = UrlInterface::URL_TYPE_LINK;

        $this->scopeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);
        $this->routeParamsResolverMock->expects($this->any())->method('getType')->willReturn($urlType);
        $this->routeParamsResolverMock->expects($this->any())->method('getRouteParams')
            ->willReturn(['key' => 'value']);
        $requestMock->expects($this->once())->method('isDirectAccessFrontendName')->willReturn(true);

        $requestMock->expects($this->once())->method('getRouteName')->willReturn('catalog');
        $requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn('product');
        $requestMock->expects($this->once())->method('getActionName')->willReturn('view');
        $routeConfigMock->expects($this->once())->method('getRouteFrontName')->willReturn('catalog');

        $url = $model->getUrl('*/*/*/key/value');
        $this->assertEquals('http://localhost/index.php/catalog/product/view/key/value/', $url);
    }

    /**
     * @return void
     */
    public function testGetDirectUrl(): void
    {
        $requestMock = $this->getRequestMock();
        $routeConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $model = $this->getUrlModel(
            [
                'scopeResolver' => $this->scopeResolverMock,
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
                'queryParamsResolver' => $this->queryParamsResolverMock,
                'request' => $requestMock,
                'routeConfig' => $routeConfigMock,
                'routeParamsPreprocessor' => $this->routeParamsPreprocessorMock
            ]
        );

        $baseUrl = 'http://localhost/index.php/';
        $urlType = UrlInterface::URL_TYPE_LINK;

        $this->scopeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);
        $this->routeParamsResolverMock->expects($this->any())->method('getType')->willReturn($urlType);

        $this->routeParamsPreprocessorMock->expects($this->once())
            ->method('execute')
            ->willReturnArgument(2);

        $requestMock->expects($this->once())->method('isDirectAccessFrontendName')->willReturn(true);

        $url = $model->getDirectUrl('direct-url');
        $this->assertEquals('http://localhost/index.php/direct-url', $url);
    }

    /**
     * @param $inputUrl
     * @param $outputUrl
     *
     * @return void
     * @dataProvider getRebuiltUrlDataProvider
     */
    public function testGetRebuiltUrl($inputUrl, $outputUrl): void
    {
        $requestMock = $this->getRequestMock();
        $model = $this->getUrlModel([
            'session' => $this->sessionMock,
            'request' => $requestMock,
            'sidResolver' => $this->sidResolverMock,
            'scopeResolver' => $this->scopeResolverMock,
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(false),
            'queryParamsResolver' => $this->queryParamsResolverMock
        ]);

        $this->queryParamsResolverMock->expects($this->once())->method('getQuery')
            ->willReturn('query=123');

        $this->assertEquals($outputUrl, $model->getRebuiltUrl($inputUrl));
    }

    /**
     * @return void
     */
    public function testGetRedirectUrl(): void
    {
        $model = $this->getUrlModel(
            [
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
                'session' => $this->sessionMock,
                'sidResolver' => $this->sidResolverMock,
                'queryParamsResolver' => $this->queryParamsResolverMock,
            ]
        );

        $this->sidResolverMock->expects($this->any())->method('getUseSessionInUrl')->willReturn(true);
        $this->sessionMock->expects($this->any())->method('getSessionIdForHost')->willReturn(false);
        $this->sidResolverMock->expects($this->any())->method('getUseSessionVar')->willReturn(true);
        $this->routeParamsResolverMock->expects($this->any())->method('hasData')->with('secure_is_forced')
            ->willReturn(true);
        $this->sidResolverMock->expects($this->never())->method('getSessionIdQueryParam');
        $this->queryParamsResolverMock->expects($this->once())
            ->method('getQuery')
            ->willReturn('foo=bar');

        $this->assertEquals('http://example.com/?foo=bar', $model->getRedirectUrl('http://example.com/'));
    }

    /**
     * @return void
     */
    public function testGetRedirectUrlWithSessionId(): void
    {
        $model = $this->getUrlModel(
            [
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(false),
                'session' => $this->sessionMock,
                'sidResolver' => $this->sidResolverMock,
                'queryParamsResolver' => $this->queryParamsResolverMock
            ]
        );

        $this->sidResolverMock->expects($this->never())->method('getUseSessionInUrl')->willReturn(true);
        $this->sessionMock->expects($this->never())->method('getSessionIdForHost')
            ->willReturn('session-id');
        $this->sidResolverMock->expects($this->never())->method('getUseSessionVar')->willReturn(false);
        $this->sidResolverMock->expects($this->never())->method('getSessionIdQueryParam');
        $this->queryParamsResolverMock->expects($this->once())
            ->method('getQuery')
            ->willReturn('foo=bar');

        $this->assertEquals('http://example.com/?foo=bar', $model->getRedirectUrl('http://example.com/'));
    }

    /**
     * @return array
     */
    public static function getRebuiltUrlDataProvider(): array
    {
        return [
            'with port' => [
                'https://example.com:88/index.php/catalog/index/view?query=123#hash',
                'https://example.com:88/index.php/catalog/index/view?query=123#hash'
            ],
            'without port' => [
                'https://example.com/index.php/catalog/index/view?query=123#hash',
                'https://example.com/index.php/catalog/index/view?query=123#hash'
            ],
            'http' => [
                'http://example.com/index.php/catalog/index/view?query=123#hash',
                'http://example.com/index.php/catalog/index/view?query=123#hash'
            ]
        ];
    }

    /**
     * @return void
     */
    public function testGetRouteUrlWithValidUrl(): void
    {
        $model = $this->getUrlModel(['routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(false)]);

        $this->routeParamsResolverMock->expects($this->never())->method('unsetData');
        $this->assertEquals('http://example.com', $model->getRouteUrl('http://example.com'));
    }

    /**
     * @param bool $result
     * @param string $referrer
     *
     * @return void
     * @dataProvider isOwnOriginUrlDataProvider
     */
    public function testIsOwnOriginUrl($result, $referrer): void
    {
        $requestMock = $this->getRequestMock();
        $this->hostChecker = $this->getMockBuilder(HostChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hostChecker->expects($this->once())->method('isOwnOrigin')->with($referrer)->willReturn($result);
        $model = $this->getUrlModel(['hostChecker' => $this->hostChecker, 'request' => $requestMock]);

        $requestMock->expects($this->once())->method('getServer')->with('HTTP_REFERER')
            ->willReturn($referrer);

        $this->assertEquals($result, $model->isOwnOriginUrl());
    }

    /**
     * @return array
     */
    public static function isOwnOriginUrlDataProvider(): array
    {
        return [
            'is origin url' => [true, 'http://localhost/'],
            'is not origin url' => [false, 'http://example.com/']
        ];
    }

    /**
     * @param string $urlType
     * @param string $configPath
     * @param bool $isSecure
     * @param int $isSecureCallCount
     * @param string $key
     *
     * @return void
     * @dataProvider getConfigDataDataProvider
     */
    public function testGetConfigData($urlType, $configPath, $isSecure, $isSecureCallCount, $key): void
    {
        $urlSecurityInfoMock = $this->getMockForAbstractClass(SecurityInfoInterface::class);
        $model = $this->getUrlModel([
            'urlSecurityInfo' => $urlSecurityInfoMock,
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
            'scopeResolver' => $this->scopeResolverMock,
            'scopeConfig' => $this->scopeConfig
        ]);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with($configPath, StoreScopeInterface::SCOPE_STORE, $this->scopeMock)
            ->willReturn('http://localhost/');
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);
        $this->scopeMock->expects($this->once())->method('isUrlSecure')->willReturn(true);
        $this->routeParamsResolverMock
            ->method('hasData')
            ->willReturnCallback(
                function ($arg1) {
                    if ($arg1 == 'secure_is_forced') {
                        return false;
                    } elseif ($arg1 == 'secure') {
                        return false;
                    }
                }
            );
        $this->routeParamsResolverMock->expects($this->any())->method('getType')
            ->willReturn($urlType);
        $this->routeParamsResolverMock->expects($this->once())
            ->method('getData')
            ->willReturn($isSecure);
        $urlSecurityInfoMock->expects($this->exactly($isSecureCallCount))->method('isSecure')
            ->willReturn(false);

        $this->assertEquals('http://localhost/', $model->getConfigData($key));
    }

    /**
     * @return array
     */
    public static function getConfigDataDataProvider(): array
    {
        return [
            'secure url' => ['some-type', 'web/secure/base_url_secure', true, 0, 'base_url_secure'],
            'unsecure url' => [
                UrlInterface::URL_TYPE_LINK,
                'web/unsecure/base_url_unsecure',
                false,
                1,
                'base_url_unsecure'
            ]
        ];
    }

    /**
     * @return void
     */
    public function testGetConfigDataWithSecureIsForcedParam(): void
    {
        $model = $this->getUrlModel([
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
            'scopeResolver' => $this->scopeResolverMock,
            'scopeConfig' => $this->scopeConfig
        ]);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                'web/secure/base_url_secure_forced',
                StoreScopeInterface::SCOPE_STORE,
                $this->scopeMock
            )
            ->willReturn('http://localhost/');
        $this->routeParamsResolverMock->expects($this->once())->method('hasData')->with('secure_is_forced')
            ->willReturn(true);
        $this->routeParamsResolverMock->expects($this->once())->method('getData')->with('secure')
            ->willReturn(true);

        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);
        $this->assertEquals('http://localhost/', $model->getConfigData('base_url_secure_forced'));
    }

    /**
     * @param string $html
     * @param string $result
     *
     * @return void
     * @dataProvider sessionUrlVarWithMatchedHostsAndBaseUrlDataProvider
     */
    public function testSessionUrlVarWithMatchedHostsAndBaseUrl($html, $result): void
    {
        $requestMock = $this->getRequestMock();
        $model = $this->getUrlModel(
            [
                'session' => $this->sessionMock,
                'request' => $requestMock,
                'sidResolver' => $this->sidResolverMock,
                'scopeResolver' => $this->scopeResolverMock,
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory()
            ]
        );

        $requestMock->expects($this->any())
            ->method('getHttpHost')
            ->willReturn('localhost');
        $this->scopeMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('http://localhost');
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);
        $this->sidResolverMock->expects($this->never())
            ->method('getSessionIdQueryParam');

        $this->assertEquals($result, $model->sessionUrlVar($html));
    }

    /**
     * @return void
     */
    public function testSessionUrlVarWithoutMatchedHostsAndBaseUrl(): void
    {
        $requestMock = $this->getRequestMock();
        $model = $this->getUrlModel(
            [
                'session' => $this->sessionMock,
                'request' => $requestMock,
                'sidResolver' => $this->sidResolverMock,
                'scopeResolver' => $this->scopeResolverMock,
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory()
            ]
        );

        $requestMock->expects($this->never())->method('getHttpHost')->willReturn('localhost');
        $this->scopeMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('http://example.com');
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);
        $this->sidResolverMock->expects($this->never())->method('getSessionIdQueryParam')
            ->willReturn('SID');
        $this->sessionMock->expects($this->never())->method('getSessionId')
            ->willReturn('session-id');

        $this->assertEquals(
            '<a href="http://example.com/">www.example.com</a>',
            $model->sessionUrlVar('<a href="http://example.com/?___SID=U">www.example.com</a>')
        );
    }

    /**
     * @return array
     */
    public static function sessionUrlVarWithMatchedHostsAndBaseUrlDataProvider(): array
    {
        return [
            [
                '<a href="http://example.com/?___SID=U?SID=session-id">www.example.com</a>',
                '<a href="http://example.com/?SID=session-id">www.example.com</a>'
            ],
            [
                '<a href="http://example.com/?___SID=U&SID=session-id">www.example.com</a>',
                '<a href="http://example.com/?SID=session-id">www.example.com</a>'
            ],
            [
                '<a href="http://example.com/?foo=bar&___SID=U?SID=session-id">www.example.com</a>',
                '<a href="http://example.com/?foo=bar?SID=session-id">www.example.com</a>'
            ],
            [
                '<a href="http://example.com/?foo=bar&___SID=U&SID=session-id">www.example.com</a>',
                '<a href="http://example.com/?foo=bar&SID=session-id">www.example.com</a>'
            ]
        ];
    }

    /**
     * @return void
     */
    public function testSetRequest(): void
    {
        $initRequestMock = $this->getRequestMock();
        $requestMock = $this->getRequestMock();
        $initRequestMock->expects($this->any())->method('getScheme')->willReturn('fake');
        $initRequestMock->expects($this->any())->method('getHttpHost')->willReturn('fake-host');
        $requestMock->expects($this->any())->method('getScheme')->willReturn('http');
        $requestMock->expects($this->any())->method('getHttpHost')->willReturn('example.com');

        $model = $this->getUrlModel(['request' => $initRequestMock]);
        $model->setRequest($requestMock);
        $this->assertEquals('http://example.com', $model->getCurrentUrl());
    }
}
