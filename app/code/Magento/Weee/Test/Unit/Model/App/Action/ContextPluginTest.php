<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\App\Action;

class ContextPluginTest extends \PHPUnit_Framework_TestCase
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

        $this->taxHelperMock = $this->getMockBuilder('Magento\Tax\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeHelperMock = $this->getMockBuilder('Magento\Weee\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeTaxMock = $this->getMockBuilder('\Magento\Weee\Model\Resource\Tax')
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpContextMock = $this->getMockBuilder('Magento\Framework\App\Http\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([
                'getDefaultTaxBillingAddress', 'getDefaultTaxShippingAddress', 'getCustomerTaxClassId', 'getWebsiteId'
            ])
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder('Magento\Framework\Module\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder('Magento\PageCache\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextPlugin = $this->objectManager->getObject(
            'Magento\Weee\Model\App\Action\ContextPlugin',
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

    public function testAroundDispatchBasedOnDefault()
    {
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
            ->willReturn('default');

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
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
            ->with(\Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null)
            ->willReturn('US');

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(\Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null)
            ->willReturn(0);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 0, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->any())
            ->method('setValue')
            ->with('weee_taxes', ['countryId' => 'US', 'regionId' => 0], 0);

        $action = $this->objectManager->getObject('Magento\Framework\App\Action\Action');
        $request = $this->getMock('\Magento\Framework\App\Request\Http', ['getActionName'], [], '', false);
        $expectedResult = 'expectedResult';
        $proceed = function ($request) use ($expectedResult) {
            return $expectedResult;
        };
        $this->contextPlugin->aroundDispatch($action, $proceed, $request);
    }

    public function testAroundDispatchBasedOnOrigin()
    {
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

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->scopeConfigMock->expects($this->at(2))
            ->method('getValue')
            ->with(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null)
            ->willReturn('US');

        $this->scopeConfigMock->expects($this->at(3))
            ->method('getValue')
            ->with(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null)
            ->willReturn(0);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 0, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->any())
            ->method('setValue')
            ->with('weee_taxes', ['countryId' => 'US', 'regionId' => 0], 0);

        $action = $this->objectManager->getObject('Magento\Framework\App\Action\Action');
        $request = $this->getMock('\Magento\Framework\App\Request\Http', ['getActionName'], [], '', false);
        $expectedResult = 'expectedResult';
        $proceed = function ($request) use ($expectedResult) {
            return $expectedResult;
        };
        $this->contextPlugin->aroundDispatch($action, $proceed, $request);
    }

    public function testAroundDispatchBasedOnBilling()
    {
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

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxBillingAddress')
            ->willReturn(['country_id' => 'US', 'region_id' => 1, 'postcode' => 11111]);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 1, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->any())
            ->method('setValue')
            ->with('weee_taxes', ['countryId' => 'US', 'regionId' => 1], 0);

        $action = $this->objectManager->getObject('Magento\Framework\App\Action\Action');
        $request = $this->getMock('\Magento\Framework\App\Request\Http', ['getActionName'], [], '', false);
        $expectedResult = 'expectedResult';
        $proceed = function ($request) use ($expectedResult) {
            return $expectedResult;
        };
        $this->contextPlugin->aroundDispatch($action, $proceed, $request);
    }

    public function testAroundDispatchBasedOnShipping()
    {
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

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxShippingAddress')
            ->willReturn(['country_id' => 'US', 'region_id' => 1, 'postcode' => 11111]);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 1, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->any())
            ->method('setValue')
            ->with('weee_taxes', ['countryId' => 'US', 'regionId' => 1], 0);

        $action = $this->objectManager->getObject('Magento\Framework\App\Action\Action');
        $request = $this->getMock('\Magento\Framework\App\Request\Http', ['getActionName'], [], '', false);
        $expectedResult = 'expectedResult';
        $proceed = function ($request) use ($expectedResult) {
            return $expectedResult;
        };
        $this->contextPlugin->aroundDispatch($action, $proceed, $request);
    }
}
