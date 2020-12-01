<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model;

use Magento\Multishipping\Model\DisableMultishipping;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * 'Disable Multishipping' model unit tests.
 */
class DisableMultishippingTest extends TestCase
{
    /**
     * @var CartInterface|MockObject
     */
    private $quoteMock;

    /**
     * @var DisableMultishipping
     */
    private $disableMultishippingModel;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->quoteMock = $this->getMockBuilder(CartInterface::class)
            ->addMethods(['getIsMultiShipping', 'setIsMultiShipping'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->disableMultishippingModel = new DisableMultishipping();
    }

    /**
     * Test 'execute' method if 'MultiShipping' mode is enabled.
     *
     * @param bool $hasShippingAssignments
     * @return void
     * @dataProvider executeWithMultishippingModeEnabledDataProvider
     */
    public function testExecuteWithMultishippingModeEnabled(bool $hasShippingAssignments): void
    {
        $shippingAssignments = $hasShippingAssignments ? ['example_shipping_assigment'] : null;

        $this->quoteMock->expects($this->once())
            ->method('getIsMultiShipping')
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('setIsMultiShipping')
            ->with(0);

        /** @var CartExtensionInterface|MockObject $extensionAttributesMock */
        $extensionAttributesMock = $this->getMockBuilder(CartExtensionInterface::class)
            ->addMethods(['getShippingAssignments', 'setShippingAssignments'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $extensionAttributesMock->expects($this->once())
            ->method('getShippingAssignments')
            ->willReturn($shippingAssignments);

        $extensionAttributesMock->expects($hasShippingAssignments ? $this->once() : $this->never())
            ->method('setShippingAssignments')
            ->with([])
            ->willReturnSelf();

        $this->quoteMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesMock);

        $this->assertTrue($this->disableMultishippingModel->execute($this->quoteMock));
    }

    /**
     * DataProvider for testExecuteWithMultishippingModeEnabled().
     *
     * @return array
     */
    public function executeWithMultishippingModeEnabledDataProvider(): array
    {
        return [
            'check_with_shipping_assignments' => [true],
            'check_without_shipping_assignments' => [false]
        ];
    }

    /**
     * Test 'execute' method if 'Multishipping' mode is disabled.
     *
     * @return void
     */
    public function testExecuteWithMultishippingModeDisabled(): void
    {
        $this->quoteMock->expects($this->once())
            ->method('getIsMultiShipping')
            ->willReturn(false);

        $this->quoteMock->expects($this->never())
            ->method('setIsMultiShipping');

        $this->quoteMock->expects($this->never())
            ->method('getExtensionAttributes');

        $this->assertFalse($this->disableMultishippingModel->execute($this->quoteMock));
    }
}
