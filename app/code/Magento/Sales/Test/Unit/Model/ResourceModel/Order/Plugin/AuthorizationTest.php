<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Sales\Model\ResourceModel\Order\Plugin\Authorization;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * @var ResourceOrder|MockObject
     */
    private $subjectMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var Authorization
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->onlyMethods(['getUserType', 'getUserId'])
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(ResourceOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCustomerId', 'getId'])
            ->getMock();
        $this->plugin = $this->objectManager->getObject(
            Authorization::class,
            ['userContext' => $this->userContextMock]
        );
    }

    public function testAfterLoad()
    {
        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn('testType');
        $this->assertEquals(
            $this->subjectMock,
            $this->plugin->afterLoad($this->subjectMock, $this->subjectMock, $this->orderMock)
        );
    }

    public function testAfterLoadWithException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity with orderId = 1');
        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->orderMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(1);
        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(2);
        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->plugin->afterLoad($this->subjectMock, $this->subjectMock, $this->orderMock);
    }
}
