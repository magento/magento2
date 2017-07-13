<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Block\Category\Plugin;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceBoxTagsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyInterface;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $timezoneInterface;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolverInterface;

    /**
     * @var \Magento\Customer\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var \Magento\Tax\Model\Calculation | \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxCalculation;

    /**
     * @var \Magento\Catalog\Block\Category\Plugin\PriceBoxTags
     */
    private $priceBoxTags;

    protected function setUp()
    {
        $this->priceCurrencyInterface = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        )->getMock();
        $this->timezoneInterface = $this->getMockBuilder(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        )->getMock();
        $this->scopeResolverInterface = $this->getMockBuilder(
            \Magento\Framework\App\ScopeResolverInterface::class
        )
            ->getMockForAbstractClass();
        $this->session = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()
            ->setMethods(
                [
                    'getCustomerGroupId',
                    'getDefaultTaxBillingAddress',
                    'getDefaultTaxShippingAddress',
                    'getCustomerTaxClassId',
                    'getCustomerId'
                ]
            )
            ->getMock();
        $this->taxCalculation = $this->getMockBuilder(\Magento\Tax\Model\Calculation::class)
            ->disableOriginalConstructor()->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->priceBoxTags = $objectManager->getObject(
            \Magento\Catalog\Block\Category\Plugin\PriceBoxTags::class,
            [
                'priceCurrency' => $this->priceCurrencyInterface,
                'dateTime' => $this->timezoneInterface,
                'scopeResolver' => $this->scopeResolverInterface,
                'customerSession' => $this->session,
                'taxCalculation' => $this->taxCalculation
            ]
        );
    }

    public function testAfterGetCacheKey()
    {
        $date = date('Ymd');
        $currencySymbol = '$';
        $result = 'result_string';
        $billingAddress = ['billing_address'];
        $shippingAddress = ['shipping_address'];
        $scopeId = 1;
        $customerGroupId = 2;
        $customerTaxClassId = 3;
        $customerId = 4;
        $rateIds = [5,6];
        $expected = implode(
            '-',
            [
                $result,
                $currencySymbol,
                $date,
                $scopeId,
                $customerGroupId,
                implode('_', $rateIds)
            ]
        );
        $priceBox = $this->getMockBuilder(\Magento\Framework\Pricing\Render\PriceBox::class)
            ->disableOriginalConstructor()->getMock();
        $this->priceCurrencyInterface->expects($this->once())->method('getCurrencySymbol')->willReturn($currencySymbol);
        $scope = $this->getMockBuilder(\Magento\Framework\App\ScopeInterface::class)->getMock();
        $this->scopeResolverInterface->expects($this->any())->method('getScope')->willReturn($scope);
        $scope->expects($this->any())->method('getId')->willReturn($scopeId);
        $dateTime = $this->getMockBuilder(\DateTime::class)->getMock();
        $this->timezoneInterface->expects($this->any())->method('scopeDate')->with($scopeId)->willReturn($dateTime);
        $dateTime->expects($this->any())->method('format')->with('Ymd')->willReturn($date);
        $this->session->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->session->expects($this->once())->method('getDefaultTaxBillingAddress')->willReturn($billingAddress);
        $this->session->expects($this->once())->method('getDefaultTaxShippingAddress')->willReturn($shippingAddress);
        $this->session->expects($this->once())->method('getCustomerTaxClassId')
            ->willReturn($customerTaxClassId);
        $this->session->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $rateRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)->getMock();
        $this->taxCalculation->expects($this->once())->method('getRateRequest')->with(
            new \Magento\Framework\DataObject($billingAddress),
            new \Magento\Framework\DataObject($shippingAddress),
            $customerTaxClassId,
            $scopeId,
            $customerId
        )->willReturn($rateRequest);
        $salableInterface = $this->getMockBuilder(\Magento\Framework\Pricing\SaleableInterface::class)
            ->setMethods(['getTaxClassId'])
            ->getMockForAbstractClass();
        $priceBox->expects($this->once())->method('getSaleableItem')->willReturn($salableInterface);
        $salableInterface->expects($this->once())->method('getTaxClassId')->willReturn($customerTaxClassId);
        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->setMethods(['getRateIds'])
            ->getMockForAbstractClass();
        $this->taxCalculation->expects($this->once())->method('getResource')->willReturn($resource);
        $resource->expects($this->once())->method('getRateIds')->with($rateRequest)->willReturn($rateIds);

        $this->assertEquals($expected, $this->priceBoxTags->afterGetCacheKey($priceBox, $result));
    }
}
