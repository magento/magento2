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
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Validation\RefundInvoiceInterface;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\SalesInventory\Model\Order\ReturnValidator;
use Magento\SalesInventory\Model\Plugin\Order\Validation\InvoiceRefundCreationArguments;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InvoiceRefundCreationArgumentsTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var InvoiceRefundCreationArguments
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
     * @var RefundInvoiceInterface|MockObject
     */
    private $refundInvoiceValidatorMock;

    /**
     * @var InvoiceInterface|MockObject
     */
    private $invoiceMock;

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
        $this->refundInvoiceValidatorMock = $this->createMock(RefundInvoiceInterface::class);
        $this->invoiceMock = $this->createMock(InvoiceInterface::class);
        $this->orderMock = $this->createMock(OrderInterface::class);
        $this->creditmemoMock = $this->createMock(CreditmemoInterface::class);

        $this->plugin = new InvoiceRefundCreationArguments($this->returnValidatorMock);
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
            $this->refundInvoiceValidatorMock,
            $this->validateResultMock,
            $this->invoiceMock,
            $this->orderMock,
            $this->creditmemoMock,
            [],
            false,
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
