<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PathConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit\Framework\MockObject\MockObject*/
    private $scopeConfigMock;
    /** @var \Magento\Framework\Url\SecurityInfoInterface | \PHPUnit\Framework\MockObject\MockObject*/
    private $urlSecurityInfoMock;
    /** @var StoreManagerInterface | \PHPUnit\Framework\MockObject\MockObject*/
    private $storeManagerMock;
    /** @var Store | \PHPUnit\Framework\MockObject\MockObject*/
    private $storeMock;
    /** @var \Magento\Store\Model\RouteConfig */
    protected $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlSecurityInfoMock = $this->getMockBuilder(\Magento\Framework\Url\SecurityInfoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockArgs = [
            'scopeConfig' => $this->scopeConfigMock,
            'urlSecurityInfo' => $this->urlSecurityInfoMock,
            'storeManager' => $this->storeManagerMock,
        ];
        $this->model = (new ObjectManager($this))->getObject(\Magento\Store\Model\PathConfig::class, $mockArgs);
    }

    public function testGetCurrentSecureUrlNoAlias()
    {
        $baseUrl = 'base-store.url/';
        $pathInfo = 'path/to/action';

        $this->storeMock->expects($this->once())->method('getBaseUrl')->with('link', true)->willReturn($baseUrl);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);

        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
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

        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
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
    public function urlSchemeProvider()
    {
        return [
            ['https://base.url', true],
            ['http://base.url', false]
        ];
    }
}
