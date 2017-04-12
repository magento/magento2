<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\App\Area;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Setup\ConfigOptionsList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class FrontNameResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Area\FrontNameResolver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\App\Config
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var string
     */
    protected $_defaultFrontName = 'defaultFrontName';

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|DeploymentConfig $deploymentConfigMock */
        $deploymentConfigMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME)
            ->will($this->returnValue($this->_defaultFrontName));
        $this->configMock = $this->getMock(\Magento\Backend\App\Config::class, [], [], '', false);
        $this->scopeConfigMock = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            [],
            '',
            false
        );
        $this->model = new FrontNameResolver($this->configMock, $deploymentConfigMock, $this->scopeConfigMock);
    }

    public function testIfCustomPathUsed()
    {
        $this->configMock->expects(
            $this->at(0)
        )->method(
            'getValue'
        )->with(
            'admin/url/use_custom_path'
        )->will(
            $this->returnValue(true)
        );
        $this->configMock->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            'admin/url/custom_path'
        )->will(
            $this->returnValue('expectedValue')
        );
        $this->assertEquals('expectedValue', $this->model->getFrontName());
    }

    public function testIfCustomPathNotUsed()
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'admin/url/use_custom_path'
        )->will(
            $this->returnValue(false)
        );
        $this->assertEquals($this->_defaultFrontName, $this->model->getFrontName());
    }

    /**
     * @param string $url
     * @param string $host
     * @param string $useCustomAdminUrl
     * @param string $customAdminUrl
     * @param string $expectedValue
     * @dataProvider hostsDataProvider
     */
    public function testIsHostBackend($url, $host, $useCustomAdminUrl, $customAdminUrl, $expectedValue)
    {
        $_SERVER['HTTP_HOST'] = $host;
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->will(
                $this->returnValueMap(
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
                            ScopeInterface::SCOPE_STORE,
                            null,
                            $customAdminUrl
                        ],
                    ]
                )
            );
        $this->assertEquals($this->model->isHostBackend(), $expectedValue);
    }

    public function hostsDataProvider()
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
            ]
        ];
    }
}
