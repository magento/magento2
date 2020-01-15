<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Category\Plugin;

use Magento\Catalog\Block\Category\Plugin\PriceBoxTags;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\PriceBox;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Tax\Model\ResourceModel\Calculation as TaxCalculationResource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceBoxTagsTest extends TestCase
{
    /**
     * @var PriceBoxTags
     */
    private $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $dateTimeMock;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    private $scopeResolverMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var TaxCalculation|MockObject
     */
    private $taxCalculationMock;

    /**
     * @var TaxCalculationResource|MockObject
     */
    private $taxCalculationResourceMock;

    /**
     * @var Currency|MockObject
     */
    private $currencyMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();
        $this->scopeResolverMock = $this->getMockBuilder(ScopeResolverInterface::class)
            ->getMockForAbstractClass();
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
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
        $this->taxCalculationMock = $this->getMockBuilder(TaxCalculation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxCalculationResourceMock = $this->getMockBuilder(TaxCalculationResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            PriceBoxTags::class,
            [
                'priceCurrency' => $this->priceCurrencyMock,
                'dateTime' => $this->dateTimeMock,
                'scopeResolver' => $this->scopeResolverMock,
                'customerSession' => $this->customerSessionMock,
                'taxCalculation' => $this->taxCalculationMock,
                'taxCalculationResource' => $this->taxCalculationResourceMock
            ]
        );
    }

    /**
     * Test for afterGetCacheKey() method
     */
    public function testAfterGetCacheKey()
    {
        $date = date('Ymd');
        $currencyCode = 'USD';
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
                $currencyCode,
                $date,
                $scopeId,
                $customerGroupId,
                implode('_', $rateIds)
            ]
        );
        $priceBoxMock = $this->getMockBuilder(PriceBox::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrencyMock->expects($this->once())
            ->method('getCurrency')
            ->willReturn($this->currencyMock);
        $this->currencyMock->expects($this->once())
            ->method('getCode')
            ->willReturn($currencyCode);

        $scopeMock = $this->getMockBuilder(ScopeInterface::class)->getMock();
        $this->scopeResolverMock->method('getScope')->willReturn($scopeMock);
        $scopeMock->method('getId')->willReturn($scopeId);

        $dateTime = $this->getMockBuilder(\DateTime::class)->getMock();
        $this->dateTimeMock->method('scopeDate')->with($scopeId)->willReturn($dateTime);
        $dateTime->method('format')->with('Ymd')->willReturn($date);

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);
        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxBillingAddress')
            ->willReturn($billingAddress);
        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxShippingAddress')
            ->willReturn($shippingAddress);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerTaxClassId')
            ->willReturn($customerTaxClassId);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $rateRequest = $this->getMockBuilder(DataObject::class)->getMock();
        $this->taxCalculationMock->expects($this->once())
            ->method('getRateRequest')
            ->with(
                new DataObject($shippingAddress),
                new DataObject($billingAddress),
                $customerTaxClassId,
                $scopeId,
                $customerId
            )
            ->willReturn($rateRequest);

        $salableInterface = $this->getMockBuilder(SaleableInterface::class)
            ->setMethods(['getTaxClassId'])
            ->getMockForAbstractClass();
        $priceBoxMock->expects($this->once())
            ->method('getSaleableItem')
            ->willReturn($salableInterface);
        $salableInterface->expects($this->once())
            ->method('getTaxClassId')
            ->willReturn($customerTaxClassId);
        $this->taxCalculationResourceMock->expects($this->once())
            ->method('getRateIds')
            ->with($rateRequest)
            ->willReturn($rateIds);

        $this->assertEquals($expected, $this->model->afterGetCacheKey($priceBoxMock, $result));
    }
}
