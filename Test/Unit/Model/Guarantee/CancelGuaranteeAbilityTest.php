<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\Guarantee;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CaseManagement;
use Magento\Signifyd\Model\CaseEntity;
use Magento\Signifyd\Model\Guarantee\CancelGuaranteeAbility;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelGuaranteeAbilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var CaseManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $caseManagement;

    /**
     * @var CancelGuaranteeAbility
     */
    private $cancelGuaranteeAbility;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->caseManagement = $this->getMockBuilder(CaseManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cancelGuaranteeAbility = new CancelGuaranteeAbility(
            $this->caseManagement,
            $this->orderRepository
        );
    }

    /**
     * Success test for Cancel Guarantee Request button
     */
    public function testIsAvailableSuccess()
    {
        $orderId = 123;

        /** @var CaseInterface|\PHPUnit_Framework_MockObject_MockObject $case */
        $case = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $case->expects($this->once())
            ->method('isGuaranteeEligible')
            ->willReturn(false);

        $case->expects($this->once())
            ->method('getGuaranteeDisposition')
            ->willReturn(CaseEntity::GUARANTEE_APPROVED);

        $this->caseManagement->expects($this->once())
            ->method('getByOrderId')
            ->with($orderId)
            ->willReturn($case);

        /** @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject $order */
        $order = $this->getMockBuilder(OrderInterface::class)
            ->getMockForAbstractClass();

        $order->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_COMPLETE);

        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($order);

        $this->assertTrue($this->cancelGuaranteeAbility->isAvailable($orderId));
    }

    /**
     * Tests case when Case entity doesn't exist for order
     */
    public function testIsAvailableWithNullCase()
    {
        $orderId = 123;

        $this->caseManagement->expects($this->once())
            ->method('getByOrderId')
            ->with($orderId)
            ->willReturn(null);

        $this->assertFalse($this->cancelGuaranteeAbility->isAvailable($orderId));
    }

    /**
     * Tests case when GuaranteeEligible for Case is true or null

     * @param mixed $guaranteeEligible
     * @dataProvider isAvailableWithGuarantyIsEligibleDataProvider
     */
    public function testIsAvailableWithGuarantyIsEligible($guaranteeEligible)
    {
        $orderId = 123;

        /** @var CaseInterface|\PHPUnit_Framework_MockObject_MockObject $case */
        $case = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $case->expects($this->once())
            ->method('isGuaranteeEligible')
            ->willReturn($guaranteeEligible);

        $this->caseManagement->expects($this->once())
            ->method('getByOrderId')
            ->with($orderId)
            ->willReturn($case);

        $this->assertFalse($this->cancelGuaranteeAbility->isAvailable($orderId));
    }

    public function isAvailableWithGuarantyIsEligibleDataProvider()
    {
        return [
            [null], [true]
        ];
    }

    /**
     * Tests case when Guarantee Disposition has Declined or Canceled states.
     *
     * @param string $guaranteeDisposition
     * @dataProvider isAvailableWithCanceledGuaranteeDataProvider
     */
    public function testIsAvailableWithCanceledGuarantee($guaranteeDisposition)
    {
        $orderId = 123;

        /** @var CaseInterface|\PHPUnit_Framework_MockObject_MockObject $case */
        $case = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $case->expects($this->once())
            ->method('isGuaranteeEligible')
            ->willReturn(false);

        $case->expects($this->once())
            ->method('getGuaranteeDisposition')
            ->willReturn($guaranteeDisposition);

        $this->caseManagement->expects($this->once())
            ->method('getByOrderId')
            ->with($orderId)
            ->willReturn($case);

        $this->assertFalse($this->cancelGuaranteeAbility->isAvailable($orderId));
    }

    public function isAvailableWithCanceledGuaranteeDataProvider()
    {
        return [
            [CaseEntity::GUARANTEE_DECLINED], [CaseEntity::GUARANTEE_CANCELED]
        ];
    }

    /**
     * Tests case when order doesn't not exist.
     */
    public function testIsAvailableWithNullOrder()
    {
        $orderId = 123;

        /** @var CaseInterface|\PHPUnit_Framework_MockObject_MockObject $case */
        $case = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $case->expects($this->once())
            ->method('isGuaranteeEligible')
            ->willReturn(false);

        $case->expects($this->once())
            ->method('getGuaranteeDisposition')
            ->willReturn(CaseEntity::GUARANTEE_APPROVED);

        $this->caseManagement->expects($this->once())
            ->method('getByOrderId')
            ->with($orderId)
            ->willReturn($case);

        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willThrowException(new NoSuchEntityException());

        $this->assertFalse($this->cancelGuaranteeAbility->isAvailable($orderId));
    }

    /**
     * Tests case when order has Canceled state.
     */
    public function testIsAvailableWithCanceledOrder()
    {
        $orderId = 123;

        /** @var CaseInterface|\PHPUnit_Framework_MockObject_MockObject $case */
        $case = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $case->expects($this->once())
            ->method('isGuaranteeEligible')
            ->willReturn(false);

        $case->expects($this->once())
            ->method('getGuaranteeDisposition')
            ->willReturn(CaseEntity::GUARANTEE_APPROVED);

        $this->caseManagement->expects($this->once())
            ->method('getByOrderId')
            ->with($orderId)
            ->willReturn($case);

        /** @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject $order */
        $order = $this->getMockBuilder(OrderInterface::class)
            ->getMockForAbstractClass();

        $order->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_CANCELED);

        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($order);

        $this->assertFalse($this->cancelGuaranteeAbility->isAvailable($orderId));
    }
}
