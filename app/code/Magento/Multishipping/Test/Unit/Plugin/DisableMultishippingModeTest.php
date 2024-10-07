<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Plugin;

use Magento\Checkout\Controller\Index\Index;
use Magento\Checkout\Model\Cart;
use Magento\Multishipping\Model\DisableMultishipping as DisableMultishippingModel;
use Magento\Multishipping\Plugin\DisableMultishippingMode;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Set of Unit Tets to cover DisableMultishippingMode
 */
class DisableMultishippingModeTest extends TestCase
{
    /**
     * @var Cart|MockObject
     */
    private $cartMock;

    /**
     * @var CartInterface|MockObject
     */
    private $quoteMock;

    /**
     * @var DisableMultishippingModel|MockObject
     */
    private $disableMultishippingMock;

    /**
     * @var DisableMultishippingMode
     */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->cartMock = $this->createMock(Cart::class);
        $this->quoteMock = $this->getMockBuilder(CartInterface::class)
            ->addMethods(['setTotalsCollectedFlag', 'getTotalsCollectedFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cartMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->disableMultishippingMock = $this->createMock(DisableMultishippingModel::class);
        $this->object = new DisableMultishippingMode(
            $this->cartMock,
            $this->disableMultishippingMock
        );
    }

    /**
     * Test 'Disable Multishipping' plugin if 'Multishipping' mode is changed.
     *
     * @param bool $totalsCollectedBefore
     * @return void
     * @dataProvider pluginWithChangedMultishippingModeDataProvider
     */
    public function testPluginWithChangedMultishippingMode(bool $totalsCollectedBefore): void
    {
        $subject = $this->createMock(Index::class);
        $this->disableMultishippingMock->expects($this->once())
            ->method('execute')
            ->with($this->quoteMock)
            ->willReturn(true);
        $this->quoteMock->expects($this->once())
            ->method('getTotalsCollectedFlag')
            ->willReturn($totalsCollectedBefore);
        $this->quoteMock->expects($totalsCollectedBefore ? $this->never() : $this->once())
            ->method('setTotalsCollectedFlag')
            ->with(false);
        $this->cartMock->expects($this->once())
            ->method('saveQuote');

        $this->object->beforeExecute($subject);
    }

    /**
     * DataProvider for testPluginWithChangedMultishippingMode().
     *
     * @return array
     */
    public static function pluginWithChangedMultishippingModeDataProvider(): array
    {
        return [
            'check_when_totals_are_collected' => [true],
            'check_when_totals_are_not_collected' => [false]
        ];
    }

    /**
     * Test 'Disable Multishipping' plugin if 'Multishipping' mode is NOT changed.
     *
     * @return void
     */
    public function testPluginWithNotChangedMultishippingMode(): void
    {
        $subject = $this->createMock(Index::class);
        $this->disableMultishippingMock->expects($this->once())
            ->method('execute')
            ->with($this->quoteMock)
            ->willReturn(false);
        $this->quoteMock->expects($this->never())
            ->method('getTotalsCollectedFlag');
        $this->quoteMock->expects($this->never())
            ->method('setTotalsCollectedFlag');
        $this->cartMock->expects($this->never())
            ->method('saveQuote');

        $this->object->beforeExecute($subject);
    }
}
