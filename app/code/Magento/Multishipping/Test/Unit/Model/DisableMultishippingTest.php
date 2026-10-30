<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Multishipping\Model\DisableMultishipping;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * 'Disable Multishipping' model unit tests.
 */
class DisableMultishippingTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Quote|MockObject
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
        $this->quoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            ['getIsMultiShipping', 'setIsMultiShipping', 'getExtensionAttributes']
        );
        $this->disableMultishippingModel = new DisableMultishipping();
    }

    /**
     * Test 'execute' method if 'MultiShipping' mode is enabled.
     *
     * @param bool $hasShippingAssignments
     * @return void
     */
    #[DataProvider('executeWithMultishippingModeEnabledDataProvider')]
    public function testExecuteWithMultishippingModeEnabled(bool $hasShippingAssignments): void
    {
        $shippingAssignments = $hasShippingAssignments ? ['example_shipping_assigment'] : null;

        $this->quoteMock->expects($this->once())
            ->method('getIsMultiShipping')
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('setIsMultiShipping')
            ->with(0);

        $extensionAttributesMock = $this->getCartExtensionMock();

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
    public static function executeWithMultishippingModeEnabledDataProvider(): array
    {
        return [
            'check_with_shipping_assignments' => [true],
            'check_without_shipping_assignments' => [false],
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

    /**
     * Build cart extension mock.
     *
     * @return MockObject
     */
    private function getCartExtensionMock(): MockObject
    {
        return $this->createPartialMockWithReflection(
            CartExtensionInterface::class,
            ['getShippingAssignments', 'setShippingAssignments']
        );
    }
}
