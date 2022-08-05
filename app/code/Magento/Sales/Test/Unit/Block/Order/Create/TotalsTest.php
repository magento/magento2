<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Order\Create;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TotalsTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $shippingAddressMock;

    /**
     * @var MockObject
     */
    protected $billingAddressMock;

    /**
     * @var Totals
     */
    protected $totals;

    /**
     * @var ObjectManager
     */
    protected $helperManager;

    /**
     * @var Quote|MockObject
     */
    protected $sessionQuoteMock;

    /**
     * @var \Magento\Quote\Model\Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->helperManager = new ObjectManager($this);
        $this->sessionQuoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'collectTotals',
                'getTotals',
                'isVirtual',
                'getBillingAddress',
                'getShippingAddress'
            ])->addMethods(['setTotalsCollectedFlag'])
            ->getMock();
        $this->shippingAddressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->billingAddressMock = $this->getMockBuilder(Address::class)
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
            Totals::class,
            ['sessionQuote' => $this->sessionQuoteMock]
        );
    }

    /**
     * @param bool $isVirtual
     *
     * @return void
     * @dataProvider totalsDataProvider
     */
    public function testGetTotals(bool $isVirtual): void
    {
        $expected = 'expected';
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
    public function totalsDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
