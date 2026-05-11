<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Order\Create;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class TotalsTest extends TestCase
{
    use MockCreationTrait;

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
     * @var QuoteModel|MockObject
     */
    protected $quoteMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->helperManager = new ObjectManager($this);
        $this->helperManager->prepareObjectManager();
        $this->sessionQuoteMock = $this->createMock(Quote::class);
        $this->quoteMock = $this->createPartialMockWithReflection(
            QuoteModel::class,
            [
                'collectTotals', 'getTotals', 'isVirtual', 'getBillingAddress', 'getShippingAddress',
                'setTotalsCollectedFlag'
            ]
        );
        $this->shippingAddressMock = $this->createMock(Address::class);
        $this->billingAddressMock = $this->createMock(Address::class);

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
     */
    #[DataProvider('totalsDataProvider')]
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
    public static function totalsDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
