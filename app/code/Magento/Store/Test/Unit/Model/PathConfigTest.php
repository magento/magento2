<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\SecurityInfoInterface;
use Magento\Store\Model\PathConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PathConfigTest extends TestCase
{
    /** @var ScopeConfigInterface|MockObject*/
    private $scopeConfigMock;
    /** @var SecurityInfoInterface|MockObject*/
    private $urlSecurityInfoMock;
    /** @var StoreManagerInterface|MockObject*/
    private $storeManagerMock;
    /** @var Store|MockObject*/
    private $storeMock;
    /** @var \Magento\Store\Model\RouteConfig */
    protected $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlSecurityInfoMock = $this->getMockBuilder(SecurityInfoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockArgs = [
            'scopeConfig' => $this->scopeConfigMock,
            'urlSecurityInfo' => $this->urlSecurityInfoMock,
            'storeManager' => $this->storeManagerMock,
        ];
        $this->model = (new ObjectManager($this))->getObject(PathConfig::class, $mockArgs);
    }

    public function testGetCurrentSecureUrlNoAlias()
    {
        $baseUrl = 'base-store.url/';
        $pathInfo = 'path/to/action';

        $this->storeMock->expects($this->once())->method('getBaseUrl')->with('link', true)->willReturn($baseUrl);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);

        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())->method('getAlias')->willReturn(null);
        $request->expects($this->once())->method('getPathInfo')->willReturn($pathInfo);
        $this->assertSame($baseUrl . $pathInfo, $this->model->getCurrentSecureUrl($request));
    }

    public function testGetCurrentSecureUrlWithAlias()
    {
        $baseUrl = 'base-store.url/';
        $alias = 'action-alias';

        $this->storeMock->expects($this->once())->method('getBaseUrl')->with('link', true)->willReturn($baseUrl);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);

        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())->method('getAlias')->willReturn($alias);
        $request->expects($this->never())->method('getPathInfo');
        $this->assertSame($baseUrl . $alias, $this->model->getCurrentSecureUrl($request));
    }

    /**
     * @dataProvider urlSchemeProvider
     * @param string $base Base Url
     * @param bool $secure Expected return value
     */
    public function testShouldBeSecureUnsecureBaseUrl($base, $secure)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Store::XML_PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE)
            ->willReturn($base);
        $this->assertSame($secure, $this->model->shouldBeSecure('path/to/action'));
    }

    /**
     * @dataProvider urlSchemeProvider
     * @param string $base Base Url
     * @param bool $secure Expected return value
     */
    public function testShouldBeSecureSecureBaseUrl($base, $secure)
    {
        $path = 'path/to/action';

        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')
            ->with(Store::XML_PATH_SECURE_IN_FRONTEND, ScopeInterface::SCOPE_STORE)
            ->willReturn($secure);

        $getValueReturnMap = [
            [Store::XML_PATH_SECURE_BASE_URL, ScopeInterface::SCOPE_STORE, null, $base],
            [Store::XML_PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE, null, 'http://unsecure.url'],
        ];

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap($getValueReturnMap);

        if ($secure) {
            $this->urlSecurityInfoMock->expects($this->once())->method('isSecure')->with($path)->willReturn($secure);
        }

        $this->assertSame($secure, $this->model->shouldBeSecure($path));
    }

    /**
     * @return array
     */
    public static function urlSchemeProvider()
    {
        return [
            ['https://base.url', true],
            ['http://base.url', false]
        ];
    }
}
