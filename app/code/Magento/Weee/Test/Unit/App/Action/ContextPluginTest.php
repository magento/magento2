<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\App\Action;

/**
 * Class ContextPluginTest
 *
 * @package Magento\Weee\Test\Unit\App\Action
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContextPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelperMock;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelperMock;

    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $weeeTaxMock;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContextMock;

    /**
     * @var \Magento\Tax\Model\Calculation\Proxy
     */
    protected $taxCalculationMock;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $cacheConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManageMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfig
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Tax\Model\App\Action\ContextPlugin
     */
    protected $contextPlugin;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->taxHelperMock = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeHelperMock = $this->getMockBuilder(\Magento\Weee\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeTaxMock = $this->getMockBuilder(\Magento\Weee\Model\Tax::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpContextMock = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                'getDefaultTaxBillingAddress', 'getDefaultTaxShippingAddress', 'getCustomerTaxClassId',
                'getWebsiteId', 'isLoggedIn'
                ]
            )
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(\Magento\Framework\Module\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(\Magento\PageCache\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextPlugin = $this->objectManager->getObject(
            \Magento\Weee\Model\App\Action\ContextPlugin::class,
            [
                'customerSession' => $this->customerSessionMock,
                'httpContext' => $this->httpContextMock,
                'weeeTax' => $this->weeeTaxMock,
                'taxHelper' => $this->taxHelperMock,
                'weeeHelper' => $this->weeeHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock,
                'storeManager' => $this->storeManagerMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    public function testBeforeDispatchBasedOnDefault()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn(true);

        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->taxHelperMock->expects($this->once())
            ->method('getTaxBasedOn')
            ->willReturn('billing');

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('US');

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(0);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 0, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with('weee_tax_region', ['countryId' => 'US', 'regionId' => 0], 0);

        $action = $this->objectManager->getObject(\Magento\Framework\App\Test\Unit\Action\Stub\ActionStub::class);
        $request = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getActionName']);

        $this->contextPlugin->beforeDispatch($action, $request);
    }

    public function testBeforeDispatchBasedOnOrigin()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn(true);

        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->taxHelperMock->expects($this->once())
            ->method('getTaxBasedOn')
            ->willReturn('origin');

        $action = $this->objectManager->getObject(\Magento\Framework\App\Test\Unit\Action\Stub\ActionStub::class);
        $request = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getActionName']);

        $this->contextPlugin->beforeDispatch($action, $request);
    }

    public function testBeforeDispatchBasedOnBilling()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn(true);

        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->taxHelperMock->expects($this->once())
            ->method('getTaxBasedOn')
            ->willReturn('billing');

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('US');

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(0);

        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxBillingAddress')
            ->willReturn(['country_id' => 'US', 'region_id' => 1]);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 1, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with('weee_tax_region', ['countryId' => 'US', 'regionId' => 1], 0);

        $action = $this->objectManager->getObject(\Magento\Framework\App\Test\Unit\Action\Stub\ActionStub::class);
        $request = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getActionName']);

        $this->contextPlugin->beforeDispatch($action, $request);
    }

    public function testBeforeDispatchBasedOnShipping()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn(true);

        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->taxHelperMock->expects($this->once())
            ->method('getTaxBasedOn')
            ->willReturn('shipping');

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('US');

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(0);

        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxShippingAddress')
            ->willReturn(['country_id' => 'US', 'region_id' => 1]);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 1, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with('weee_tax_region', ['countryId' => 'US', 'regionId' => 1], 0);

        $action = $this->objectManager->getObject(\Magento\Framework\App\Test\Unit\Action\Stub\ActionStub::class);
        $request = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getActionName']);

        $this->contextPlugin->beforeDispatch($action, $request);
    }
}
