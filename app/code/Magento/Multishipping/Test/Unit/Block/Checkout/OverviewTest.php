<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Multishipping\Block\Checkout\Overview;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\Quote\TotalsReader;
use Magento\Sales\Model\Order\Total;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OverviewTest extends TestCase
{
    /**
     * @var Overview
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var MockObject
     */
    protected $addressMock;

    /**
     * @var MockObject
     */
    protected $totalsReaderMock;

    /**
     * @var MockObject
     */
    protected $totalsCollectorMock;

    /**
     * @var MockObject
     */
    protected $checkoutMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    private $urlBuilderMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getAddressType'])
            ->onlyMethods(['getShippingMethod', 'getShippingRateByCode', 'getAllVisibleItems', 'getTotals'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceCurrencyMock =
            $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->totalsReaderMock = $this->createMock(TotalsReader::class);
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);
        $this->checkoutMock =
            $this->createMock(Multishipping::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->model = $objectManager->getObject(
            Overview::class,
            [
                'priceCurrency' => $this->priceCurrencyMock,
                'totalsCollector' => $this->totalsCollectorMock,
                'totalsReader' => $this->totalsReaderMock,
                'multishipping' => $this->checkoutMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    public function testGetShippingRateByCode()
    {
        $rateMock = $this->createMock(Rate::class);
        $this->addressMock->expects($this->once())
            ->method('getShippingMethod')->willReturn('shipping method');
        $this->addressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with('shipping method')
            ->willReturn($rateMock);

        $this->assertEquals($rateMock, $this->model->getShippingAddressRate($this->addressMock));
    }

    public function testGetShippingRateByCodeWithEmptyRate()
    {
        $this->addressMock->expects($this->once())
            ->method('getShippingMethod')->willReturn('shipping method');
        $this->addressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with('shipping method')
            ->willReturn(false);

        $this->assertFalse($this->model->getShippingAddressRate($this->addressMock));
    }

    public function testGetShippingAddressItems()
    {
        $this->addressMock->expects($this->once())->method('getAllVisibleItems')->willReturn(['expected array']);
        $this->assertEquals(['expected array'], $this->model->getShippingAddressItems($this->addressMock));
    }

    public function testGetShippingAddressTotals()
    {
        $totalMock = $this->getMockBuilder(Total::class)
            ->addMethods(['getCode', 'setTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $totalMock->expects($this->once())->method('getCode')->willReturn('grand_total');
        $this->addressMock->expects($this->once())->method('getAddressType')->willReturn(Address::TYPE_BILLING);
        $this->addressMock->expects($this->once())->method('getTotals')->willReturn([$totalMock]);
        $totalMock->expects($this->once())->method('setTitle')->with('Total');

        $this->assertEquals([$totalMock], $this->model->getShippingAddressTotals($this->addressMock));
    }

    public function testGetShippingAddressTotalsWithNotBillingAddress()
    {
        $totalMock = $this->getMockBuilder(Total::class)
            ->addMethods(['getCode', 'setTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $totalMock->expects($this->once())->method('getCode')->willReturn('grand_total');
        $this->addressMock->expects($this->once())->method('getAddressType')->willReturn('not billing');
        $this->addressMock->expects($this->once())->method('getTotals')->willReturn([$totalMock]);
        $totalMock->expects($this->once())->method('setTitle')->with('Total for this address');

        $this->assertEquals([$totalMock], $this->model->getShippingAddressTotals($this->addressMock));
    }

    /**
     * @param MockObject $address
     * @return MockObject
     */
    protected function getTotalsMock($address)
    {
        $totalMock = $this->getMockBuilder(Total::class)
            ->addMethods(['getCode', 'setTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $totalsAddressMock = $this->createMock(\Magento\Quote\Model\Quote\Address\Total::class);
        $this->checkoutMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $this->totalsCollectorMock
            ->expects($this->once())
            ->method('collectAddressTotals')
            ->with($this->quoteMock, $address)->willReturn($totalsAddressMock);
        $totalsAddressMock->expects($this->once())->method('getData')->willReturn([]);
        $this->totalsReaderMock
            ->expects($this->once())
            ->method('fetch')
            ->with($this->quoteMock, [])
            ->willReturn([$totalMock]);
        return $totalMock;
    }

    public function testGetVirtualProductEditUrl()
    {
        $url = 'http://example.com';
        $this->urlBuilderMock->expects($this->once())->method('getUrl')->with('checkout/cart', [])->willReturn($url);
        $this->assertEquals($url, $this->model->getVirtualProductEditUrl());
    }
}
