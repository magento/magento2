<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesInventory\Test\Unit\Model\Plugin\Order\Validation;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Validation\RefundOrderInterface;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\SalesInventory\Model\Order\ReturnValidator;
use Magento\SalesInventory\Model\Plugin\Order\Validation\OrderRefundCreationArguments;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderRefundCreationArgumentsTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var OrderRefundCreationArguments
     */
    private $plugin;

    /**
     * @var ReturnValidator|MockObject
     */
    private $returnValidatorMock;

    /**
     * @var CreditmemoCreationArgumentsExtensionInterface|MockObject
     */
    private $extensionAttributesMock;

    /**
     * @var CreditmemoCreationArgumentsInterface|MockObject
     */
    private $creditmemoCreationArgumentsMock;

    /**
     * @var RefundOrderInterface|MockObject
     */
    private $refundOrderValidatorMock;

    /**
     * @var ValidatorResultInterface|MockObject
     */
    private $validateResultMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var CreditmemoInterface|MockObject
     */
    private $creditmemoMock;

    protected function setUp(): void
    {
        $this->returnValidatorMock = $this->createMock(ReturnValidator::class);
        $this->creditmemoCreationArgumentsMock = $this->createMock(CreditmemoCreationArgumentsInterface::class);
        $this->extensionAttributesMock = $this->createPartialMockWithReflection(
            CreditmemoCreationArgumentsExtensionInterface::class,
            ['getReturnToStockItems']
        );
        $this->validateResultMock = $this->createMock(ValidatorResultInterface::class);
        $this->refundOrderValidatorMock = $this->createMock(RefundOrderInterface::class);
        $this->orderMock = $this->createMock(OrderInterface::class);
        $this->creditmemoMock = $this->createMock(CreditmemoInterface::class);

        $this->plugin = new OrderRefundCreationArguments($this->returnValidatorMock);
    }

    #[DataProvider('dataProvider')]
    public function testAfterValidation($erroMessage)
    {
        $returnToStockItems = [1];
        $this->creditmemoCreationArgumentsMock->expects($this->exactly(3))
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->extensionAttributesMock->expects($this->exactly(2))
            ->method('getReturnToStockItems')
            ->willReturn($returnToStockItems);

        $this->returnValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn($erroMessage);

        $this->validateResultMock->expects($erroMessage ? $this->once() : $this->never())
            ->method('addMessage')
            ->with($erroMessage);

        $this->plugin->afterValidate(
            $this->refundOrderValidatorMock,
            $this->validateResultMock,
            $this->orderMock,
            $this->creditmemoMock,
            [],
            false,
            false,
            null,
            $this->creditmemoCreationArgumentsMock
        );
    }

    /**
     * @return array
     */
    public static function dataProvider()
    {
        return [
            'withErrors' => ['Error!'],
            'withoutErrors' => ['null'],
        ];
    }
}
