<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Backend\Model\Session\Quote as AdminSessionQuote;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\OrderEditUsageOffset;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderEditUsageOffsetTest extends TestCase
{
    /**
     * @var State|MockObject
     */
    private $appState;

    /**
     * @var AdminSessionQuote|MockObject
     */
    private $adminSessionQuote;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var Address|MockObject
     */
    private $address;

    /**
     * @var OrderEditUsageOffset
     */
    private $orderEditUsageOffset;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->appState = $this->createMock(State::class);
        $this->adminSessionQuote = $this->getMockBuilder(AdminSessionQuote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', 'getOrder'])
            ->getMock();
        $this->quote = $this->createPartialMock(Quote::class, ['getData']);
        $this->address = $this->createPartialMock(Address::class, ['getQuote']);
        $this->address->method('getQuote')->willReturn($this->quote);
        $this->orderEditUsageOffset = new OrderEditUsageOffset($this->appState, $this->adminSessionQuote);
    }

    /**
     * @return void
     */
    public function testGetOffsetReturnsOneForMatchingRuleInAdminArea(): void
    {
        $ruleId = 10;
        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_ADMINHTML);
        $this->quote->expects($this->once())
            ->method('getData')
            ->with(Create::ORIGINAL_ORDER_APPLIED_RULE_IDS)
            ->willReturn('10,11');

        $this->assertSame(1, $this->orderEditUsageOffset->getOffset($this->address, $ruleId));
    }

    /**
     * @return void
     */
    public function testGetOffsetReturnsZeroOutsideAdminArea(): void
    {
        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_FRONTEND);
        $this->quote->expects($this->never())->method('getData');
        $this->adminSessionQuote->expects($this->never())->method('getData');

        $this->assertSame(0, $this->orderEditUsageOffset->getOffset($this->address, 10));
    }

    /**
     * @return void
     */
    public function testGetOffsetReturnsZeroWhenAreaCodeIsUnavailable(): void
    {
        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willThrowException(new LocalizedException(__('Area code is not set')));
        $this->quote->expects($this->never())->method('getData');

        $this->assertSame(0, $this->orderEditUsageOffset->getOffset($this->address, 10));
    }

    /**
     * @return void
     */
    public function testGetOffsetForQuoteFallsBackToSessionOrderAppliedRules(): void
    {
        $ruleId = 7;
        $order = $this->createPartialMock(Order::class, ['getId', 'getAppliedRuleIds']);

        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_ADMINHTML);
        $this->quote->expects($this->once())
            ->method('getData')
            ->with(Create::ORIGINAL_ORDER_APPLIED_RULE_IDS)
            ->willReturn(null);
        $this->adminSessionQuote->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(static function (string $key) {
                return match ($key) {
                    'order_id' => 100,
                    'reordered' => null,
                    default => null,
                };
            });
        $this->adminSessionQuote->expects($this->once())->method('getOrder')->willReturn($order);
        $order->expects($this->once())->method('getId')->willReturn(100);
        $order->expects($this->once())->method('getAppliedRuleIds')->willReturn('7,8');

        $this->assertSame(1, $this->orderEditUsageOffset->getOffsetForQuote($this->quote, $ruleId));
    }

    /**
     * @return void
     */
    public function testGetOffsetForQuoteReturnsZeroWhenSessionHasNoOrderId(): void
    {
        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_ADMINHTML);
        $this->quote->expects($this->once())
            ->method('getData')
            ->with(Create::ORIGINAL_ORDER_APPLIED_RULE_IDS)
            ->willReturn(null);
        $this->adminSessionQuote->expects($this->once())
            ->method('getData')
            ->with('order_id')
            ->willReturn(null);
        $this->adminSessionQuote->expects($this->never())->method('getOrder');

        $this->assertSame(0, $this->orderEditUsageOffset->getOffsetForQuote($this->quote, 10));
    }

    /**
     * @return void
     */
    public function testGetOffsetForQuoteReturnsZeroWhenSessionIsReorder(): void
    {
        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_ADMINHTML);
        $this->quote->expects($this->once())
            ->method('getData')
            ->with(Create::ORIGINAL_ORDER_APPLIED_RULE_IDS)
            ->willReturn(null);
        $this->adminSessionQuote->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(static function (string $key) {
                return match ($key) {
                    'order_id' => 100,
                    'reordered' => 100,
                    default => null,
                };
            });
        $this->adminSessionQuote->expects($this->never())->method('getOrder');

        $this->assertSame(0, $this->orderEditUsageOffset->getOffsetForQuote($this->quote, 10));
    }

    /**
     * @return void
     */
    public function testGetOffsetForQuoteReturnsZeroWhenOriginalOrderHasNoAppliedRules(): void
    {
        $order = $this->createPartialMock(Order::class, ['getId', 'getAppliedRuleIds']);

        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_ADMINHTML);
        $this->quote->expects($this->once())
            ->method('getData')
            ->with(Create::ORIGINAL_ORDER_APPLIED_RULE_IDS)
            ->willReturn(null);
        $this->adminSessionQuote->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(static function (string $key) {
                return match ($key) {
                    'order_id' => 100,
                    'reordered' => null,
                    default => null,
                };
            });
        $this->adminSessionQuote->expects($this->once())->method('getOrder')->willReturn($order);
        $order->expects($this->once())->method('getId')->willReturn(100);
        $order->expects($this->once())->method('getAppliedRuleIds')->willReturn(null);

        $this->assertSame(0, $this->orderEditUsageOffset->getOffsetForQuote($this->quote, 10));
    }

    /**
     * @return void
     */
    public function testGetOffsetForQuoteReturnsZeroWhenSessionOrderHasNoId(): void
    {
        $order = $this->createPartialMock(Order::class, ['getId', 'getAppliedRuleIds']);

        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_ADMINHTML);
        $this->quote->expects($this->once())
            ->method('getData')
            ->with(Create::ORIGINAL_ORDER_APPLIED_RULE_IDS)
            ->willReturn(null);
        $this->adminSessionQuote->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(static function (string $key) {
                return match ($key) {
                    'order_id' => 100,
                    'reordered' => null,
                    default => null,
                };
            });
        $this->adminSessionQuote->expects($this->once())->method('getOrder')->willReturn($order);
        $order->expects($this->once())->method('getId')->willReturn(null);
        $order->expects($this->never())->method('getAppliedRuleIds');

        $this->assertSame(0, $this->orderEditUsageOffset->getOffsetForQuote($this->quote, 10));
    }

    /**
     * @return void
     */
    public function testGetOffsetForRuleIdFallsBackToSessionOrderAppliedRules(): void
    {
        $ruleId = 7;
        $order = $this->createPartialMock(Order::class, ['getId', 'getAppliedRuleIds']);

        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(Area::AREA_ADMINHTML);
        $this->adminSessionQuote->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(static function (string $key) {
                return match ($key) {
                    'order_id' => 100,
                    'reordered' => null,
                    default => null,
                };
            });
        $this->adminSessionQuote->expects($this->once())->method('getOrder')->willReturn($order);
        $order->expects($this->once())->method('getId')->willReturn(100);
        $order->expects($this->once())->method('getAppliedRuleIds')->willReturn('7,8');

        $this->assertSame(1, $this->orderEditUsageOffset->getOffsetForRuleId($ruleId));
    }
}
