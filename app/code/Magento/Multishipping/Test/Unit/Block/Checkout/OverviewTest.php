<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Multishipping\Block\Checkout\Overview;
use Magento\Quote\Model\Quote\Address;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OverviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Overview
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsCollectorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->addressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            [
                'getShippingMethod',
                'getShippingRateByCode',
                'getAllVisibleItems',
                'getTotals',
                'getAddressType',
                '__wakeup'
            ],
            [],
            '',
            false);

        $this->priceCurrencyMock =
            $this->getMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class, [], [], '', false);
        $this->totalsReaderMock = $this->getMock(\Magento\Quote\Model\Quote\TotalsReader::class, [], [], '', false);
        $this->totalsCollectorMock = $this->getMock(
            \Magento\Quote\Model\Quote\TotalsCollector::class,
            [],
            [],
            '',
            false
        );
        $this->checkoutMock =
            $this->getMock(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class, [], [], '', false);
        $this->quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->urlBuilderMock = $this->getMock(\Magento\Framework\UrlInterface::class);
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
        $rateMock = $this->getMock(\Magento\Quote\Model\Quote\Address\Rate::class, [], [], '', false);
        $this->addressMock->expects($this->once())
            ->method('getShippingMethod')->will($this->returnValue('shipping method'));
        $this->addressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with('shipping method')
            ->willReturn($rateMock);

        $this->assertEquals($rateMock, $this->model->getShippingAddressRate($this->addressMock));
    }

    public function testGetShippingRateByCodeWithEmptyRate()
    {
        $this->addressMock->expects($this->once())
            ->method('getShippingMethod')->will($this->returnValue('shipping method'));
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
        $totalMock = $this->getMock(
            \Magento\Sales\Model\Order\Total::class,
            ['getCode', 'setTitle', '__wakeup'],
            [],
            '',
            false
        );
        $totalMock->expects($this->once())->method('getCode')->willReturn('grand_total');
        $this->addressMock->expects($this->once())->method('getAddressType')->willReturn(Address::TYPE_BILLING);
        $this->addressMock->expects($this->once())->method('getTotals')->willReturn([$totalMock]);
        $totalMock->expects($this->once())->method('setTitle')->with('Total');

        $this->assertEquals([$totalMock], $this->model->getShippingAddressTotals($this->addressMock));
    }

    public function testGetShippingAddressTotalsWithNotBillingAddress()
    {
        $totalMock = $this->getMock(
            \Magento\Sales\Model\Order\Total::class,
            ['getCode', 'setTitle', '__wakeup'],
            [],
            '',
            false
        );
        $totalMock->expects($this->once())->method('getCode')->willReturn('grand_total');
        $this->addressMock->expects($this->once())->method('getAddressType')->willReturn('not billing');
        $this->addressMock->expects($this->once())->method('getTotals')->willReturn([$totalMock]);
        $totalMock->expects($this->once())->method('setTitle')->with('Total for this address');

        $this->assertEquals([$totalMock], $this->model->getShippingAddressTotals($this->addressMock));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $address
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTotalsMock($address)
    {
        $totalMock = $this->getMock(
            \Magento\Sales\Model\Order\Total::class,
            [
                'getCode',
                'setTitle',
                '__wakeup'
            ],
            [],
            '',
            false);
        $totalsAddressMock = $this->getMock(\Magento\Quote\Model\Quote\Address\Total::class, [], [], '', false);
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
