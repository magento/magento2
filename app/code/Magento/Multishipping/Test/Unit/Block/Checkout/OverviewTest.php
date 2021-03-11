<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Multishipping\Block\Checkout\Overview;
use Magento\Quote\Model\Quote\Address;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OverviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Overview
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $totalsReaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $totalsCollectorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $checkoutMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $urlBuilderMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->addressMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, [
                'getShippingMethod',
                'getShippingRateByCode',
                'getAllVisibleItems',
                'getTotals',
                'getAddressType',
                '__wakeup'
            ]);

        $this->priceCurrencyMock =
            $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->totalsReaderMock = $this->createMock(\Magento\Quote\Model\Quote\TotalsReader::class);
        $this->totalsCollectorMock = $this->createMock(\Magento\Quote\Model\Quote\TotalsCollector::class);
        $this->checkoutMock =
            $this->createMock(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class);
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->model = $objectManager->getObject(
            \Magento\Multishipping\Block\Checkout\Overview::class,
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
        $rateMock = $this->createMock(\Magento\Quote\Model\Quote\Address\Rate::class);
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
        $totalMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Total::class,
            ['getCode', 'setTitle', '__wakeup']
        );
        $totalMock->expects($this->once())->method('getCode')->willReturn('grand_total');
        $this->addressMock->expects($this->once())->method('getAddressType')->willReturn(Address::TYPE_BILLING);
        $this->addressMock->expects($this->once())->method('getTotals')->willReturn([$totalMock]);
        $totalMock->expects($this->once())->method('setTitle')->with('Total');

        $this->assertEquals([$totalMock], $this->model->getShippingAddressTotals($this->addressMock));
    }

    public function testGetShippingAddressTotalsWithNotBillingAddress()
    {
        $totalMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Total::class,
            ['getCode', 'setTitle', '__wakeup']
        );
        $totalMock->expects($this->once())->method('getCode')->willReturn('grand_total');
        $this->addressMock->expects($this->once())->method('getAddressType')->willReturn('not billing');
        $this->addressMock->expects($this->once())->method('getTotals')->willReturn([$totalMock]);
        $totalMock->expects($this->once())->method('setTitle')->with('Total for this address');

        $this->assertEquals([$totalMock], $this->model->getShippingAddressTotals($this->addressMock));
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $address
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTotalsMock($address)
    {
        $totalMock = $this->createPartialMock(\Magento\Sales\Model\Order\Total::class, [
                'getCode',
                'setTitle',
                '__wakeup'
            ]);
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
