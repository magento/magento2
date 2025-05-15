<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\GraphQl\Model\Query\Context;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Reorder\Reorder;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\SalesGraphQl\Model\Resolver\Reorder as Subject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ReorderTest extends TestCase
{
    /**
     * @var Subject|MockObject
     */
    private $subject;

    /**
     * @var ContextExtensionInterface|MockObject
     */
    private $extensionAttributesMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactory;

    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockManager;

    /**
     * @var Reorder|MockObject
     */
    private $reorder;

    protected function setUp(): void
    {
        $this->reorder = $this->createMock(Reorder::class);
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->lockManager = $this->createMock(LockManagerInterface::class);
        $this->contextMock = $this->createMock(Context::class);

        $this->subject = new Subject(
            $this->reorder,
            $this->orderFactory,
            $this->lockManager
        );
    }

    public function testResolve(): void
    {
        $fieldMock = $this->createMock(Field::class);
        $resolveInfoMock = $this->createMock(ResolveInfo::class);
        $args = ['orderNumber' => '00000010'];
        $value = [];

        $this->prepareCommonFlow();

        $this->lockManager->expects($this->once())
            ->method('lock')
            ->willReturn(true);
        $this->lockManager->expects($this->once())
            ->method('unlock')
            ->willReturn(true);

        $result = $this->subject->resolve($fieldMock, $this->contextMock, $resolveInfoMock, $value, $args);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('cart', $result);
        $this->assertArrayHasKey('userInputErrors', $result);
        $this->assertEmpty($result['userInputErrors']);
    }

    public function testResolveLockedAndThrowsError(): void
    {
        $fieldMock = $this->createMock(Field::class);
        $resolveInfoMock = $this->createMock(ResolveInfo::class);
        $args = ['orderNumber' => '00000010'];
        $value = [];

        $this->prepareCommonFlow();

        $this->lockManager->expects($this->once())
            ->method('lock')
            ->willReturn(false);
        $this->lockManager->expects($this->never())
            ->method('unlock');

        $exceptionMessage = 'Sorry, there has been an error processing your request. Please try again later.';
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->subject->resolve($fieldMock, $this->contextMock, $resolveInfoMock, $value, $args);
    }

    private function prepareCommonFlow()
    {
        $contextCustomerId = 1;
        $orderCustomerId = 1;

        $this->extensionAttributesMock = $this->getMockBuilder(ContextExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIsCustomer', 'getStore'])
            ->getMockForAbstractClass();
        $this->extensionAttributesMock->expects($this->once())
            ->method('getIsCustomer')
            ->willReturn(true);

        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->extensionAttributesMock->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $this->contextMock->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($contextCustomerId);

        $order = $this->createMock(Order::class);
        $order->expects($this->once())
            ->method('loadByIncrementIdAndStoreId')
            ->willReturnSelf();
        $order->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($orderCustomerId);
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($order);
    }
}
