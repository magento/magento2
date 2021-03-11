<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Block\Order\Create;

/**
 * Class TotalsTest
 */
class TotalsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $shippingAddressMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $billingAddressMock;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Create\Totals
     */
    protected $totals;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helperManager;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionQuoteMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * Init
     */
    protected function setUp(): void
    {
        $this->helperManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sessionQuoteMock = $this->getMockBuilder(\Magento\Backend\Model\Session\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setTotalsCollectedFlag',
                'collectTotals',
                'getTotals',
                'isVirtual',
                'getBillingAddress',
                'getShippingAddress'
            ])
            ->getMock();
        $this->shippingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->billingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressMock);
        $this->quoteMock->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->sessionQuoteMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->totals = $this->helperManager->getObject(
            \Magento\Sales\Block\Adminhtml\Order\Create\Totals::class,
            ['sessionQuote' => $this->sessionQuoteMock]
        );
    }

    /**
     * @dataProvider totalsDataProvider
     */
    public function testGetTotals($isVirtual)
    {
        $expected = 'expected';
        $this->quoteMock->expects($this->at(1))->method('collectTotals');
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn($isVirtual);
        if ($isVirtual) {
            $this->billingAddressMock->expects($this->once())->method('getTotals')->willReturn($expected);
        } else {
            $this->shippingAddressMock->expects($this->once())->method('getTotals')->willReturn($expected);
        }
        $this->assertEquals($expected, $this->totals->getTotals());
    }

    /**
     * @return array
     */
    public function totalsDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
