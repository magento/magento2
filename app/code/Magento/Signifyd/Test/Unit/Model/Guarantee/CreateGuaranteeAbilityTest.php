<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\Guarantee;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CaseManagement;
use Magento\Signifyd\Model\Guarantee\CreateGuaranteeAbility;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateGuaranteeAbilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var CaseManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $caseManagement;

    /**
     * @var CreateGuaranteeAbility
     */
    private $createGuaranteeAbility;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dateTimeFactory = new DateTimeFactory();
        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->caseManagement = $this->getMockBuilder(CaseManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->createGuaranteeAbility = new CreateGuaranteeAbility(
            $this->caseManagement,
            $this->orderRepository,
            $this->dateTimeFactory
        );
    }

    public function testIsAvailableSuccess()
    {
        $orderId = 123;
        $orderCreatedAt = $this->getDateAgo(6);

        /** @var CaseInterface|\PHPUnit_Framework_MockObject_MockObject $case */
        $case = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $case->expects($this->once())
            ->method('isGuaranteeEligible')
            ->willReturn(true);

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
        $order->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($orderCreatedAt);

        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($order);

        $this->assertTrue($this->createGuaranteeAbility->isAvailable($orderId));
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

        $this->assertFalse($this->createGuaranteeAbility->isAvailable($orderId));
    }

    /**
     * Tests case when GuaranteeEligible for Case is false
     */
    public function testIsAvailableWithGuarantyEligibleFalse()
    {
        $orderId = 123;

        /** @var CaseInterface|\PHPUnit_Framework_MockObject_MockObject $case */
        $case = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $case->expects($this->once())
            ->method('isGuaranteeEligible')
            ->willReturn(false);

        $this->caseManagement->expects($this->once())
            ->method('getByOrderId')
            ->with($orderId)
            ->willReturn($case);

        $this->assertFalse($this->createGuaranteeAbility->isAvailable($orderId));
    }

    /**
     * Tests case when GuaranteeEligible for Case is false
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
            ->willReturn(true);

        $this->caseManagement->expects($this->once())
            ->method('getByOrderId')
            ->with($orderId)
            ->willReturn($case);

        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willThrowException(new NoSuchEntityException());

        $this->assertFalse($this->createGuaranteeAbility->isAvailable($orderId));
    }

    /**
     * Tests case when order has Canceled Or Closed states.
     *
     * @param string $state
     * @dataProvider isAvailableWithCanceledOrderDataProvider
     */
    public function testIsAvailableWithCanceledOrder($state)
    {
        $orderId = 123;

        /** @var CaseInterface|\PHPUnit_Framework_MockObject_MockObject $case */
        $case = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $case->expects($this->once())
            ->method('isGuaranteeEligible')
            ->willReturn(true);

        $this->caseManagement->expects($this->once())
            ->method('getByOrderId')
            ->with($orderId)
            ->willReturn($case);

        /** @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject $order */
        $order = $this->getMockBuilder(OrderInterface::class)
            ->getMockForAbstractClass();
        $order->expects($this->once())
            ->method('getState')
            ->willReturn($state);

        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($order);

        $this->assertFalse($this->createGuaranteeAbility->isAvailable($orderId));
    }

    /**
     * @return array
     */
    public function isAvailableWithCanceledOrderDataProvider()
    {
        return [
            [Order::STATE_CANCELED], [Order::STATE_CLOSED]
        ];
    }

    public function testIsAvailableWithOldOrder()
    {
        $orderId = 123;
        $orderCreatedAt = $this->getDateAgo(8);

        /** @var CaseInterface|\PHPUnit_Framework_MockObject_MockObject $case */
        $case = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $case->expects($this->once())
            ->method('isGuaranteeEligible')
            ->willReturn(true);

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
        $order->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($orderCreatedAt);

        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($order);

        $this->assertFalse($this->createGuaranteeAbility->isAvailable($orderId));
    }

    /**
     * Returns date N days ago
     *
     * @param int $days number of days that will be deducted from the current date
     * @return string
     */
    private function getDateAgo($days)
    {
        $createdAtTime = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
        $createdAtTime->sub(new \DateInterval('P' . $days . 'D'));

        return $createdAtTime->format('Y-m-d h:i:s');
    }
}
