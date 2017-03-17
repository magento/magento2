<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Sales\Model\Order;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\Plugin\Authorization;

class AuthorizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var ResourceOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var Authorization
     */
    private $plugin;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->userContextMock = $this->getMockBuilder(UserContextInterface::class)
            ->setMethods(['getUserType', 'getUserId'])
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(ResourceOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getId'])
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

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with orderId = 1
     */
    public function testAfterLoadWithException()
    {
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
