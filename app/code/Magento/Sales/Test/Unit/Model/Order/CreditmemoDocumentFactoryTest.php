<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCommentInterface;
use Magento\Sales\Api\Data\CreditmemoCommentInterfaceFactory;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoDocumentFactoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CreditmemoDocumentFactory|MockObject
     */
    private $factory;

    /**
     * @var CreditmemoFactory|MockObject
     */
    private $creditmemoFactoryMock;

    /**
     * @var CreditmemoCommentInterfaceFactory|MockObject
     */
    private $commentFactoryMock;

    /**
     * @var HydratorPool|MockObject
     */
    private $hydratorPoolMock;

    /**
     * @var HydratorInterface|MockObject
     */
    private $hydratorMock;

    /**
     * @var \Magento\Sales\Model\Order|MockObject
     */
    private $orderMock;

    /**
     * @var Invoice|MockObject
     */
    private $invoiceMock;

    /**
     * @var CreditmemoItemCreationInterface|MockObject
     */
    private $creditmemoItemCreationMock;

    /**
     * @var CreditmemoCommentCreationInterface|MockObject
     */
    private $commentCreationMock;

    /**
     * @var CreditmemoCreationArgumentsInterface|MockObject
     */
    private $commentCreationArgumentsMock;

    /**
     * @var Order\Creditmemo|MockObject
     */
    private $creditmemoMock;

    /**
     * @var CreditmemoCommentInterface|MockObject
     */
    private $commentMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->creditmemoFactoryMock = $this->getMockBuilder(CreditmemoFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentFactoryMock =
            $this->getMockBuilder(CreditmemoCommentInterfaceFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->hydratorPoolMock = $this->getMockBuilder(HydratorPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItemCreationMock = $this->getMockBuilder(CreditmemoItemCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hydratorMock = $this->getMockBuilder(HydratorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->commentCreationArgumentsMock = $this->getMockBuilder(CreditmemoCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->commentCreationMock = $this->getMockBuilder(CreditmemoCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditmemoMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(11);

        $this->commentMock = $this->getMockBuilder(CreditmemoCommentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                array_merge(
                    get_class_methods(CreditmemoCommentInterface::class),
                    ['setStoreId', 'setCreditmemo']
                )
            )
            ->getMock();
        $this->factory = $this->objectManager->getObject(
            CreditmemoDocumentFactory::class,
            [
                'creditmemoFactory' => $this->creditmemoFactoryMock,
                'commentFactory' => $this->commentFactoryMock,
                'hydratorPool' => $this->hydratorPoolMock,
                'orderRepository' => $this->orderRepositoryMock
            ]
        );
    }

    private function commonFactoryFlow()
    {
        $this->creditmemoItemCreationMock->expects($this->once())
            ->method('getOrderItemId')
            ->willReturn(7);
        $this->creditmemoItemCreationMock->expects($this->once())
            ->method('getQty')
            ->willReturn(3);
        $this->hydratorPoolMock->expects($this->exactly(2))
            ->method('getHydrator')
            ->willReturnMap(
                [
                    [CreditmemoCreationArgumentsInterface::class, $this->hydratorMock],
                    [CreditmemoCommentCreationInterface::class, $this->hydratorMock],
                ]
            );
        $this->hydratorMock->expects($this->exactly(2))
            ->method('extract')
            ->willReturnMap([
                [$this->commentCreationArgumentsMock, ['shipping_amount' => '20.00']],
                [$this->commentCreationMock, ['comment' => 'text']]
            ]);
        $this->commentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'data' => [
                        'comment' => 'text'
                    ]
                ]
            )
            ->willReturn($this->commentMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(11);
        $this->creditmemoMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);
        $this->commentMock->expects($this->once())
            ->method('setParentId')
            ->with(11)
            ->willReturnSelf();
        $this->commentMock->expects($this->once())
            ->method('setStoreId')
            ->with(1)
            ->willReturnSelf();
        $this->commentMock->expects($this->once())
            ->method('setIsCustomerNotified')
            ->with(true)
            ->willReturnSelf();
        $this->commentMock->expects($this->once())
            ->method('setCreditmemo')
            ->with($this->creditmemoMock)
            ->willReturnSelf();
    }

    public function testCreateFromOrder()
    {
        $this->commonFactoryFlow();
        $this->creditmemoFactoryMock->expects($this->once())
            ->method('createByOrder')
            ->with(
                $this->orderMock,
                [
                    'shipping_amount' => '20.00',
                    'qtys' => [7 => 3]
                ]
            )
            ->willReturn($this->creditmemoMock);
        $this->factory->createFromOrder(
            $this->orderMock,
            [$this->creditmemoItemCreationMock],
            $this->commentCreationMock,
            true,
            $this->commentCreationArgumentsMock
        );
    }

    public function testCreateFromInvoice()
    {
        $this->commonFactoryFlow();
        $this->creditmemoFactoryMock->expects($this->once())
            ->method('createByInvoice')
            ->with(
                $this->invoiceMock,
                [
                    'shipping_amount' => '20.00',
                    'qtys' => [7 => 3]
                ]
            )
            ->willReturn($this->creditmemoMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);
        $this->invoiceMock->expects($this->once())
            ->method('setOrder')
            ->with($this->orderMock)
            ->willReturnSelf();
        $this->factory->createFromInvoice(
            $this->invoiceMock,
            [$this->creditmemoItemCreationMock],
            $this->commentCreationMock,
            true,
            $this->commentCreationArgumentsMock
        );
    }
}
