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
use Magento\Multishipping\Plugin\DisableMultishippingMode;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Set of Unit Tets to cover DisableMultishippingMode
 */
class DisableMultishippingModeTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $cartMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

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
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setIsMultiShipping', 'getIsMultiShipping'])
            ->onlyMethods(['__wakeUp', 'getExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->object = new DisableMultishippingMode($this->cartMock);
    }

    /**
     * Tests turn off multishipping on multishipping quote.
     *
     * @return void
     */
    public function testExecuteTurnsOffMultishippingModeOnMultishippingQuote(): void
    {
        $subject = $this->createMock(Index::class);
        $extensionAttributes = $this->getMockBuilder(CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShippingAssignments', 'getShippingAssignments'])
            ->getMockForAbstractClass();
        $extensionAttributes->method('getShippingAssignments')
            ->willReturn(
                $this->getMockForAbstractClass(ShippingAssignmentInterface::class)
            );
        $extensionAttributes->expects($this->once())
            ->method('setShippingAssignments')
            ->with([]);
        $this->quoteMock->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->quoteMock->expects($this->once())
            ->method('getIsMultiShipping')->willReturn(1);
        $this->quoteMock->expects($this->once())
            ->method('setIsMultiShipping')
            ->with(0);
        $this->cartMock->expects($this->once())
            ->method('saveQuote');

        $this->object->beforeExecute($subject);
    }

    /**
     * Tests turn off multishipping on non-multishipping quote.
     *
     * @return void
     */
    public function testExecuteTurnsOffMultishippingModeOnNotMultishippingQuote(): void
    {
        $subject = $this->createMock(Index::class);
        $this->quoteMock->expects($this->once())->method('getIsMultiShipping')->willReturn(0);
        $this->quoteMock->expects($this->never())->method('setIsMultiShipping');
        $this->cartMock->expects($this->never())->method('saveQuote');
        $this->object->beforeExecute($subject);
    }
}
