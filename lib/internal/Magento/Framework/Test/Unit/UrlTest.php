<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit;

use Magento\Framework\Url\HostChecker;

/**
 * Test class for Magento\Framework\Url
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Url\RouteParamsResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $routeParamsResolverMock;

    /**
     * @var \Magento\Framework\Url\RouteParamsPreprocessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $routeParamsPreprocessorMock;

    /**
     * @var \Magento\Framework\Url\ScopeResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeResolverMock;

    /**
     * @var \Magento\Framework\Url\ScopeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeMock;

    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $queryParamsResolverMock;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sidResolverMock;

    /**
     * @var \Magento\Framework\Session\Generic|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Url\ModifierInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlModifier;

    /**
     * @var HostChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostChecker;

    protected function setUp(): void
    {
        $this->routeParamsResolverMock = $this->createPartialMock(
            \Magento\Framework\Url\RouteParamsResolver::class,
            ['getType', 'hasData', 'getData', 'getRouteParams', 'unsetData']
        );

        $escaperMock = $this->createMock(\Magento\Framework\ZendEscaper::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $objectManager->setBackwardCompatibleProperty($this->routeParamsResolverMock, 'escaper', $escaperMock);

        $this->routeParamsPreprocessorMock = $this->getMockForAbstractClass(
            \Magento\Framework\Url\RouteParamsPreprocessorInterface::class,
            ['unsetData'],
            '',
            false,
            true,
            true,
            []
        );

        $this->scopeResolverMock = $this->createMock(\Magento\Framework\Url\ScopeResolverInterface::class);
        $this->scopeMock = $this->createMock(\Magento\Framework\Url\ScopeInterface::class);
        $this->queryParamsResolverMock = $this->createMock(\Magento\Framework\Url\QueryParamsResolverInterface::class);
        $this->sidResolverMock = $this->createMock(\Magento\Framework\Session\SidResolverInterface::class);
        $this->sessionMock = $this->createMock(\Magento\Framework\Session\Generic::class);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->urlModifier = $this->createMock(\Magento\Framework\Url\ModifierInterface::class);
        $this->urlModifier->expects($this->any())
            ->method('execute')
            ->willReturnArgument(0);
    }

    /**
     * @param bool $resolve
     * @return \Magento\Framework\Url\RouteParamsResolverFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRouteParamsResolverFactory($resolve = true)
    {
        $routeParamsResolverFactoryMock = $this->createMock(\Magento\Framework\Url\RouteParamsResolverFactory::class);
        if ($resolve) {
            $routeParamsResolverFactoryMock->expects($this->any())->method('create')
                ->willReturn($this->routeParamsResolverMock);
        }
        return $routeParamsResolverFactoryMock;
    }

    /**
     * @param array $mockMethods
     * @return \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRequestMock()
    {
        return $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @param array $arguments
     * @return \Magento\Framework\Url
     */
    protected function getUrlModel($arguments = [])
    {
        $arguments = array_merge($arguments, ['scopeType' => \Magento\Store\Model\ScopeInterface::SCOPE_STORE]);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(\Magento\Framework\Url::class, $arguments);

        $modelProperty = (new \ReflectionClass(get_class($model)))
            ->getProperty('urlModifier');

        $modelProperty->setAccessible(true);
        $modelProperty->setValue($model, $this->urlModifier);

        $zendEscaper = new \Magento\Framework\ZendEscaper();
        $escaper = new \Magento\Framework\Escaper();
        $objectManager->setBackwardCompatibleProperty($escaper, 'escaper', $zendEscaper);
        $objectManager->setBackwardCompatibleProperty($model, 'escaper', $escaper);

        return $model;
    }

    /**
     * @param string $httpHost
     * @param string $url
     * @dataProvider getCurrentUrlProvider
     */
    public function testGetCurrentUrl($httpHost, $url)
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
    public function getCurrentUrlProvider()
    {
        return [
            'without_port' => ['example.com', 'http://example.com/fancy_uri'],
            'default_port' => ['example.com:80', 'http://example.com/fancy_uri'],
            'custom_port' => ['example.com:8080', 'http://example.com:8080/fancy_uri']
        ];
    }

    public function testGetBaseUrlNotLinkType()
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

    public function testGetUrlValidateFilter()
    {
        $model = $this->getUrlModel();
        $this->assertEquals('http://test.com', $model->getUrl('http://test.com'));
    }

    /**
     * @param string|array|bool $query
     * @param string $queryResult
     * @param string $returnUri
     * @dataProvider getUrlDataProvider
     */
    public function testGetUrl($query, $queryResult, $returnUri)
    {
        $requestMock = $this->getRequestMock();
        $routeConfigMock = $this->createMock(\Magento\Framework\App\Route\ConfigInterface::class);
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
        $urlType = \Magento\Framework\UrlInterface::URL_TYPE_LINK;

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
            '_scope' => $this->getMockForAbstractClass(\Magento\Store\Api\Data\StoreInterface::class),
            '_fragment' => 'anchor',
            '_escape' => 1,
            '_query' => $query,
            '_nosid' => 0,
            'id' => 100
        ]);
        $this->assertEquals($returnUri, $url);
    }

    public function testGetUrlIdempotentSetRoutePath()
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

    public function testGetUrlIdempotentSetRouteName()
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

    public function testGetUrlRouteHasParams()
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

    public function testGetUrlRouteUseRewrite()
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
    public function getUrlDataProvider()
    {
        return [
            'string query' => [
                'foo=bar',
                'foo=bar',
                'http://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor',
            ],
            'array query' => [
                ['foo' => 'bar'],
                'foo=bar',
                'http://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor',
            ],
            'without query' => [
                false,
                '',
                'http://localhost/index.php/catalog/product/view/id/100/#anchor'
            ],
        ];
    }

    public function testGetUrlWithAsterisksPath()
    {
        $requestMock = $this->getRequestMock();
        $routeConfigMock = $this->createMock(\Magento\Framework\App\Route\ConfigInterface::class);
        $model = $this->getUrlModel(
            [
                'scopeResolver' => $this->scopeResolverMock,
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
                'queryParamsResolver' => $this->queryParamsResolverMock,
                'request' => $requestMock, 'routeConfig' => $routeConfigMock,
            ]
        );

        $baseUrl = 'http://localhost/index.php/';
        $urlType = \Magento\Framework\UrlInterface::URL_TYPE_LINK;

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

    public function testGetDirectUrl()
    {
        $requestMock = $this->getRequestMock();
        $routeConfigMock = $this->createMock(\Magento\Framework\App\Route\ConfigInterface::class);
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
        $urlType = \Magento\Framework\UrlInterface::URL_TYPE_LINK;

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
     * @param string $inputUrl
     * @dataProvider getRebuiltUrlDataProvider
     */
    public function testGetRebuiltUrl($inputUrl, $outputUrl)
    {
        $requestMock = $this->getRequestMock();
        $model = $this->getUrlModel([
            'session' => $this->sessionMock,
            'request' => $requestMock,
            'sidResolver' => $this->sidResolverMock,
            'scopeResolver' => $this->scopeResolverMock,
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(false),
            'queryParamsResolver' => $this->queryParamsResolverMock,
        ]);

        $this->queryParamsResolverMock->expects($this->once())->method('getQuery')
            ->willReturn('query=123');

        $this->assertEquals($outputUrl, $model->getRebuiltUrl($inputUrl));
    }

    public function testGetRedirectUrl()
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

    public function testGetRedirectUrlWithSessionId()
    {
        $model = $this->getUrlModel(
            [
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(false),
                'session' => $this->sessionMock,
                'sidResolver' => $this->sidResolverMock,
                'queryParamsResolver' => $this->queryParamsResolverMock,
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
    public function getRebuiltUrlDataProvider()
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

    public function testGetRouteUrlWithValidUrl()
    {
        $model = $this->getUrlModel(['routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(false)]);

        $this->routeParamsResolverMock->expects($this->never())->method('unsetData');
        $this->assertEquals('http://example.com', $model->getRouteUrl('http://example.com'));
    }

    /**
     * @param bool $result
     * @param string $referrer
     * @dataProvider isOwnOriginUrlDataProvider
     */
    public function testIsOwnOriginUrl($result, $referrer)
    {
        $requestMock = $this->getRequestMock();
        $this->hostChecker = $this->getMockBuilder(HostChecker::class)
            ->disableOriginalConstructor()->getMock();
        $this->hostChecker->expects($this->once())->method('isOwnOrigin')->with($referrer)->willReturn($result);
        $model = $this->getUrlModel(['hostChecker' => $this->hostChecker, 'request' => $requestMock]);

        $requestMock->expects($this->once())->method('getServer')->with('HTTP_REFERER')
            ->willReturn($referrer);

        $this->assertEquals($result, $model->isOwnOriginUrl());
    }

    /**
     * @return array
     */
    public function isOwnOriginUrlDataProvider()
    {
        return [
            'is origin url' => [true, 'http://localhost/'],
            'is not origin url' => [false, 'http://example.com/'],
        ];
    }

    /**
     * @param string $urlType
     * @param string $configPath
     * @param bool $isSecure
     * @param int $isSecureCallCount
     * @param string $key
     * @dataProvider getConfigDataDataProvider
     */
    public function testGetConfigData($urlType, $configPath, $isSecure, $isSecureCallCount, $key)
    {
        $urlSecurityInfoMock = $this->createMock(\Magento\Framework\Url\SecurityInfoInterface::class);
        $model = $this->getUrlModel([
            'urlSecurityInfo' => $urlSecurityInfoMock,
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
            'scopeResolver' => $this->scopeResolverMock,
            'scopeConfig' => $this->scopeConfig,
        ]);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with($this->equalTo($configPath), \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->scopeMock)
            ->willReturn('http://localhost/');
        $this->routeParamsResolverMock->expects($this->at(0))->method('hasData')->with('secure_is_forced')
            ->willReturn(false);
        $this->scopeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scopeMock);
        $this->scopeMock->expects($this->once())->method('isUrlSecure')->willReturn(true);
        $this->routeParamsResolverMock->expects($this->at(1))->method('hasData')->with('secure')
            ->willReturn(false);
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
    public function getConfigDataDataProvider()
    {
        return [
            'secure url' => ['some-type', 'web/secure/base_url_secure', true, 0, 'base_url_secure'],
            'unsecure url' => [
                \Magento\Framework\UrlInterface::URL_TYPE_LINK,
                'web/unsecure/base_url_unsecure',
                false,
                1,
                'base_url_unsecure',
            ],
        ];
    }

    public function testGetConfigDataWithSecureIsForcedParam()
    {
        $model = $this->getUrlModel([
            'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
            'scopeResolver' => $this->scopeResolverMock,
            'scopeConfig' => $this->scopeConfig,
        ]);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                'web/secure/base_url_secure_forced',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
     * @dataProvider sessionUrlVarWithMatchedHostsAndBaseUrlDataProvider
     */
    public function testSessionUrlVarWithMatchedHostsAndBaseUrl($html, $result)
    {
        $requestMock = $this->getRequestMock();
        $model = $this->getUrlModel(
            [
                'session' => $this->sessionMock,
                'request' => $requestMock,
                'sidResolver' => $this->sidResolverMock,
                'scopeResolver' => $this->scopeResolverMock,
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
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

    public function testSessionUrlVarWithoutMatchedHostsAndBaseUrl()
    {
        $requestMock = $this->getRequestMock();
        $model = $this->getUrlModel(
            [
                'session' => $this->sessionMock,
                'request' => $requestMock,
                'sidResolver' => $this->sidResolverMock,
                'scopeResolver' => $this->scopeResolverMock,
                'routeParamsResolverFactory' => $this->getRouteParamsResolverFactory(),
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
    public function sessionUrlVarWithMatchedHostsAndBaseUrlDataProvider()
    {
        return [
            [
                '<a href="http://example.com/?___SID=U?SID=session-id">www.example.com</a>',
                '<a href="http://example.com/?SID=session-id">www.example.com</a>',
            ],
            [
                '<a href="http://example.com/?___SID=U&SID=session-id">www.example.com</a>',
                '<a href="http://example.com/?SID=session-id">www.example.com</a>',
            ],
            [
                '<a href="http://example.com/?foo=bar&___SID=U?SID=session-id">www.example.com</a>',
                '<a href="http://example.com/?foo=bar?SID=session-id">www.example.com</a>',
            ],
            [
                '<a href="http://example.com/?foo=bar&___SID=U&SID=session-id">www.example.com</a>',
                '<a href="http://example.com/?foo=bar&SID=session-id">www.example.com</a>',
            ],
        ];
    }

    public function testSetRequest()
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
