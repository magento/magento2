<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Helper;

use Magento\Config\Model\Config;
use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Helper\Context;
use Magento\Paypal\Helper\Backend;
use Magento\Paypal\Model\Config\StructurePlugin;

class BackendTest extends \PHPUnit\Framework\TestCase
{
    const SCOPE = 'website';

    const SCOPE_ID = 1;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $directoryHelperMock;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $backendConfig;

    /**
     * @var ScopeDefiner|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeDefiner;

    /**
     * @var Backend
     */
    private $helper;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->context->expects(static::once())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->directoryHelperMock = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendConfig = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeDefiner = $this->getMockBuilder(\Magento\Config\Model\Config\ScopeDefiner::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new Backend(
            $this->context,
            $this->directoryHelperMock,
            $this->backendConfig,
            $this->scopeDefiner
        );
    }

    public function testGetConfigurationCountryCodeFromRequest()
    {
        $this->configurationCountryCodePrepareRequest('US');
        $this->configurationCountryCodeAssertResult('US');
    }

    /**
     * @param string|null $request
     * @dataProvider getConfigurationCountryCodeFromConfigDataProvider
     */
    public function testGetConfigurationCountryCodeFromConfig($request)
    {
        $this->configurationCountryCodePrepareRequest($request);
        $this->configurationCountryCodePrepareConfig('GB');
        $this->configurationCountryCodeAssertResult('GB');
    }

    /**
     * @return array
     */
    public function getConfigurationCountryCodeFromConfigDataProvider()
    {
        return [
            [null],
            ['not country code'],
        ];
    }

    /**
     * @param string|null $request
     * @param string|null|false $config
     * @param string|null $default
     * @dataProvider getConfigurationCountryCodeFromDefaultDataProvider
     */
    public function testGetConfigurationCountryCodeFromDefault($request, $config, $default)
    {
        $this->configurationCountryCodePrepareRequest($request);
        $this->configurationCountryCodePrepareConfig($config);
        $this->directoryHelperMock->expects($this->once())
            ->method('getDefaultCountry')
            ->willReturn($default);
        $this->configurationCountryCodeAssertResult($default);
    }

    /**
     * @return array
     */
    public function getConfigurationCountryCodeFromDefaultDataProvider()
    {
        return [
            [null, false, 'DE'],
            ['not country code', false, 'DE'],
            ['not country code', '', 'any final result']
        ];
    }

    /**
     * Prepare request for test
     *
     * @param string|null $request
     */
    private function configurationCountryCodePrepareRequest($request)
    {
        $this->request->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    [StructurePlugin::REQUEST_PARAM_COUNTRY, null, $request],
                    [self::SCOPE, null, self::SCOPE_ID]
                ]
            );
    }

    /**
     * Prepare backend config for test
     *
     * @param string|null|false $config
     */
    private function configurationCountryCodePrepareConfig($config)
    {

        $this->scopeDefiner->expects($this->once())
            ->method('getScope')
            ->willReturn(self::SCOPE);

        $this->backendConfig->expects($this->once())
            ->method('setData')
            ->with(self::SCOPE, self::SCOPE_ID);

        $this->backendConfig->expects($this->once())
            ->method('getConfigDataValue')
            ->with(\Magento\Paypal\Block\Adminhtml\System\Config\Field\Country::FIELD_CONFIG_PATH)
            ->willReturn($config);
    }

    /**
     * Assert result of getConfigurationCountryCode method
     *
     * @param string $expected
     */
    private function configurationCountryCodeAssertResult($expected)
    {
        $this->assertEquals($expected, $this->helper->getConfigurationCountryCode());
    }
}
