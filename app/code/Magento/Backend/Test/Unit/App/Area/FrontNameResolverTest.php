<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\App\Area;

use Laminas\Uri\Uri;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\App\Config;
use Magento\Backend\Setup\ConfigOptionsList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontNameResolverTest extends TestCase
{
    /**
     * @var FrontNameResolver
     */
    protected $model;

    /**
     * @var MockObject|Config
     */
    protected $configMock;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject|Uri
     */
    protected $uri;

    /**
     * @var MockObject|Http
     */
    protected $request;

    /**
     * @var string
     */
    protected $_defaultFrontName = 'defaultFrontName';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        /** @var MockObject|DeploymentConfig $deploymentConfigMock */
        $deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME)
            ->willReturn($this->_defaultFrontName);
        $this->uri = $this->createMock(Uri::class);

        $this->request = $this->createMock(Http::class);

        $this->configMock = $this->createMock(Config::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->model = new FrontNameResolver(
            $this->configMock,
            $deploymentConfigMock,
            $this->scopeConfigMock,
            $this->uri,
            $this->request
        );
    }

    /**
     * @return void
     */
    public function testIfCustomPathUsed(): void
    {
        $this->configMock->expects($this->once())
            ->method('isSetFlag')
            ->with(FrontNameResolver::XML_PATH_USE_CUSTOM_ADMIN_PATH)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(FrontNameResolver::XML_PATH_CUSTOM_ADMIN_PATH)
            ->willReturn('expectedValue');

        $this->assertEquals('expectedValue', $this->model->getFrontName());
    }

    /**
     * @return void
     */
    public function testIfCustomPathNotUsed(): void
    {
        $this->configMock->expects($this->once())
            ->method('isSetFlag')
            ->with(FrontNameResolver::XML_PATH_USE_CUSTOM_ADMIN_PATH)
            ->willReturn(false);

        $this->assertEquals($this->_defaultFrontName, $this->model->getFrontName());
    }

    /**
     * @param string $url
     * @param string|null $host
     * @param string $useCustomAdminUrl
     * @param string $customAdminUrl
     * @param bool $expectedValue
     *
     * @return void
     * @dataProvider hostsDataProvider
     */
    public function testIsHostBackend(
        string $url,
        ?string $host,
        string $useCustomAdminUrl,
        string $customAdminUrl,
        bool $expectedValue
    ): void {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->willReturn($useCustomAdminUrl);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturnMap(
                [
                    [Store::XML_PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE, null, $url],
                    [
                        FrontNameResolver::XML_PATH_USE_CUSTOM_ADMIN_URL,
                        ScopeInterface::SCOPE_STORE,
                        null,
                        $useCustomAdminUrl
                    ],
                    [
                        FrontNameResolver::XML_PATH_CUSTOM_ADMIN_URL,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        $customAdminUrl
                    ]
                ]
            );

        $this->request->expects($this->any())
            ->method('getServer')
            ->willReturn($host);

        $urlParts = [];
        $this->uri->expects($this->once())
            ->method('parse')
            ->willReturnCallback(
                function ($url) use (&$urlParts) {
                    $urlParts = parse_url($url);
                }
            );
        $this->uri->expects($this->once())
            ->method('getScheme')
            ->willReturnCallback(
                function () use (&$urlParts) {
                    return array_key_exists('scheme', $urlParts) ? $urlParts['scheme'] : '';
                }
            );
        $this->uri->expects($this->once())
            ->method('getHost')
            ->willReturnCallback(
                function () use (&$urlParts) {
                    return array_key_exists('host', $urlParts) ? $urlParts['host'] : '';
                }
            );
        $this->uri->expects($this->once())
            ->method('getPort')
            ->willReturnCallback(
                function () use (&$urlParts) {
                    return array_key_exists('port', $urlParts) ? $urlParts['port'] : '';
                }
            );

        $this->assertEquals($expectedValue, $this->model->isHostBackend());
    }

    /**
     * Test the case when backend url is not set.
     *
     * @return void
     */
    public function testIsHostBackendWithEmptyHost(): void
    {
        $this->request->expects($this->any())
            ->method('getServer')
            ->willReturn('magento2.loc');
        $this->uri->expects($this->once())
            ->method('getHost')
            ->willReturn(null);

        $this->assertEquals($this->model->isHostBackend(), false);
    }

    /**
     * @return array
     */
    public static function hostsDataProvider(): array
    {
        return [
            'withoutPort' => [
                'url' => 'http://magento2.loc/',
                'host' => 'magento2.loc',
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => true
            ],
            'withPort' => [
                'url' => 'http://magento2.loc:8080/',
                'host' => 'magento2.loc:8080',
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => true
            ],
            'withStandartPortInUrlWithoutPortInHost' => [
                'url' => 'http://magento2.loc:80/',
                'host' => 'magento2.loc',
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => true
            ],
            'withoutStandartPortInUrlWithPortInHost' => [
                'url' => 'https://magento2.loc/',
                'host' => 'magento2.loc:443',
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => true
            ],
            'differentHosts' => [
                'url' => 'http://m2.loc/',
                'host' => 'magento2.loc',
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => false
            ],
            'differentPortsOnOneHost' => [
                'url' => 'http://magento2.loc/',
                'host' => 'magento2.loc:8080',
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => false
            ],
            'withCustomAdminUrl' => [
                'url' => 'http://magento2.loc/',
                'host' => 'myhost.loc',
                'useCustomAdminUrl' => '1',
                'customAdminUrl' => 'https://myhost.loc/',
                'expectedValue' => true
            ],
            'withCustomAdminUrlWrongHost' => [
                'url' => 'http://magento2.loc/',
                'host' => 'SomeOtherHost.loc',
                'useCustomAdminUrl' => '1',
                'customAdminUrl' => 'https://myhost.loc/',
                'expectedValue' => false
            ],
            'withEmptyHost' => [
                'url' => 'http://magento2.loc/',
                'host' => null,
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => false
            ]
        ];
    }
}
