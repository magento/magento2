<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Test\Unit\Model\Plugin\Order\Validation;

use Magento\SalesInventory\Model\Order\ReturnValidator;
use Magento\SalesInventory\Model\Plugin\Order\Validation\OrderRefundCreationArguments;
use Magento\Sales\Model\Order\Validation\RefundOrderInterface;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class OrderRefundCreationArgumentsTest
 */
class OrderRefundCreationArgumentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderRefundCreationArguments
     */
    private $plugin;

    /**
     * @var ReturnValidator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $returnValidatorMock;

    /**
     * @var CreditmemoCreationArgumentsExtensionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $extensionAttributesMock;

    /**
     * @var CreditmemoCreationArgumentsInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $creditmemoCreationArgumentsMock;

    /**
     * @var RefundOrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $refundOrderValidatorMock;

    /**
     * @var ValidatorResultInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validateResultMock;

    /**
     * @var OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var CreditmemoInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $creditmemoMock;

    protected function setUp(): void
    {
        $this->returnValidatorMock = $this->getMockBuilder(ReturnValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditmemoCreationArgumentsMock = $this->getMockBuilder(CreditmemoCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->extensionAttributesMock = $this->getMockBuilder(CreditmemoCreationArgumentsExtensionInterface::class)
            ->setMethods(['getReturnToStockItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validateResultMock = $this->getMockBuilder(ValidatorResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->refundOrderValidatorMock = $this->getMockBuilder(RefundOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->plugin = new OrderRefundCreationArguments($this->returnValidatorMock);
    }

    /**
     * @dataProvider dataProvider
     */
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
    public function dataProvider()
    {
        return [
            'withErrors' => ['Error!'],
            'withoutErrors' => ['null'],
        ];
    }
}
