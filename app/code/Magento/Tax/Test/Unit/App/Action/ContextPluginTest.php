<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\App\Action;

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
     * Module manager
     *
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManagerMock;

    /**
     * Cache config
     *
     * @var \Magento\PageCache\Model\Config
     */
    private $cacheConfigMock;

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

        $this->weeeTaxMock = $this->getMockBuilder('\Magento\Weee\Model\Tax')
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpContextMock = $this->getMockBuilder('Magento\Framework\App\Http\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxCalculationMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\Proxy')
            ->disableOriginalConstructor()
            ->setMethods(['getTaxRates'])
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([
                'getDefaultTaxBillingAddress', 'getDefaultTaxShippingAddress', 'getCustomerTaxClassId',
                'getWebsiteId', 'isLoggedIn'
            ])
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder('Magento\Framework\Module\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder('Magento\PageCache\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextPlugin = $this->objectManager->getObject(
            'Magento\Tax\Model\App\Action\ContextPlugin',
            [
                'customerSession' => $this->customerSessionMock,
                'httpContext' => $this->httpContextMock,
                'calculation' => $this->taxCalculationMock,
                'weeeTax' => $this->weeeTaxMock,
                'taxHelper' => $this->taxHelperMock,
                'weeeHelper' => $this->weeeHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock
            ]
        );
    }

    /**
     * @param bool $cache
     * @param bool $taxEnabled
     * @param bool $loggedIn
     * @dataProvider dataProviderAroundDispatch
     */
    public function testAroundDispatch($cache, $taxEnabled, $loggedIn)
    {
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn($loggedIn);

        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn($cache);

        $this->cacheConfigMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($cache);

        if ($cache && $loggedIn) {
            $this->taxHelperMock->expects($this->any())
                ->method('isCatalogPriceDisplayAffectedByTax')
                ->willReturn($taxEnabled);

            if ($taxEnabled) {
                $this->customerSessionMock->expects($this->once())
                    ->method('getDefaultTaxBillingAddress')
                    ->willReturn(['country_id' => 1, 'region_id' => 1, 'postcode' => 11111]);
                $this->customerSessionMock->expects($this->once())
                    ->method('getDefaultTaxShippingAddress')
                    ->willReturn(['country_id' => 1, 'region_id' => 1, 'postcode' => 11111]);
                $this->customerSessionMock->expects($this->once())
                    ->method('getCustomerTaxClassId')
                    ->willReturn(1);

                $this->taxCalculationMock->expects($this->once())
                    ->method('getTaxRates')
                    ->with(
                        ['country_id' => 1, 'region_id' => 1, 'postcode' => 11111],
                        ['country_id' => 1, 'region_id' => 1, 'postcode' => 11111],
                        1
                    )
                    ->willReturn([]);

                $this->httpContextMock->expects($this->any())
                    ->method('setValue')
                    ->with('tax_rates', [], 0);
            }

            $action = $this->objectManager->getObject('Magento\Framework\App\Test\Unit\Action\Stub\ActionStub');
            $request = $this->getMock('\Magento\Framework\App\Request\Http', ['getActionName'], [], '', false);
            $expectedResult = 'expectedResult';
            $proceed = function ($request) use ($expectedResult) {
                return $expectedResult;
            };
            $this->contextPlugin->aroundDispatch($action, $proceed, $request);
        }
    }

    /**
     * @return array
     */
    public function dataProviderAroundDispatch()
    {
        return [
            [false, false, false],
            [true, true, false],
            [true, true, true],
            [true, false, true],
            [true, true, true]
        ];
    }
}
