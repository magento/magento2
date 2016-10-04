<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentPackageInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\Order\Shipment\NotifierInterface;
use Magento\Sales\Model\Order\Shipment\OrderRegistrarInterface;
use Magento\Sales\Model\Order\Validation\ShipOrderInterface;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Sales\Model\ShipOrder;
use Psr\Log\LoggerInterface;

/**
 * Class ShipOrderTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ShipOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var ShipmentDocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentDocumentFactoryMock;

    /**
     * @var ShipOrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipOrderValidatorMock;

    /**
     * @var OrderRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRegistrarMock;

    /**
     * @var OrderStateResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderStateResolverMock;

    /**
     * @var OrderConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ShipmentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentRepositoryMock;

    /**
     * @var NotifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notifierInterfaceMock;

    /**
     * @var ShipOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var ShipmentCreationArgumentsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentCommentCreationMock;

    /**
     * @var ShipmentCommentCreationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentCreationArgumentsMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var ShipmentTrackCreationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $trackMock;

    /**
     * @var ShipmentPackageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageMock;

    /**
     * @var ValidatorResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationMessagesMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentDocumentFactoryMock = $this->getMockBuilder(ShipmentDocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRegistrarMock = $this->getMockBuilder(OrderRegistrarInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderStateResolverMock = $this->getMockBuilder(OrderStateResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(OrderConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentRepositoryMock = $this->getMockBuilder(ShipmentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->notifierInterfaceMock = $this->getMockBuilder(NotifierInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentCommentCreationMock = $this->getMockBuilder(ShipmentCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentCreationArgumentsMock = $this->getMockBuilder(ShipmentCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->packageMock = $this->getMockBuilder(ShipmentPackageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->trackMock = $this->getMockBuilder(ShipmentTrackCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipOrderValidatorMock = $this->getMockBuilder(ShipOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationMessagesMock = $this->getMockBuilder(ValidatorResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasMessages', 'getMessages', 'addMessage'])
            ->getMock();
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $helper->getObject(
            ShipOrder::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'orderRepository' => $this->orderRepositoryMock,
                'shipmentDocumentFactory' => $this->shipmentDocumentFactoryMock,
                'orderStateResolver' => $this->orderStateResolverMock,
                'config' => $this->configMock,
                'shipmentRepository' => $this->shipmentRepositoryMock,
                'shipOrderValidator' => $this->shipOrderValidatorMock,
                'notifierInterface' => $this->notifierInterfaceMock,
                'logger' => $this->loggerMock,
                'orderRegistrar' => $this->orderRegistrarMock
            ]
        );
    }

    /**
     * @param int $orderId
     * @param array $items
     * @param bool $notify
     * @param bool $appendComment
     * @throws \Magento\Sales\Exception\CouldNotShipException
     * @throws \Magento\Sales\Exception\DocumentValidationException
     * @dataProvider dataProvider
     */
    public function testExecute($orderId, $items, $notify, $appendComment)
    {
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);
        $this->shipmentDocumentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items,
                [$this->trackMock],
                $this->shipmentCommentCreationMock,
                ($appendComment && $notify),
                [$this->packageMock],
                $this->shipmentCreationArgumentsMock
            )->willReturn($this->shipmentMock);
        $this->shipOrderValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->orderMock,
                $this->shipmentMock,
                $items,
                $notify,
                $appendComment,
                $this->shipmentCommentCreationMock,
                [$this->trackMock],
                [$this->packageMock]
            )
            ->willReturn($this->validationMessagesMock);
        $hasMessages = false;
        $this->validationMessagesMock->expects($this->once())
            ->method('hasMessages')->willReturn($hasMessages);
        $this->orderRegistrarMock->expects($this->once())
            ->method('register')
            ->with($this->orderMock, $this->shipmentMock)
            ->willReturn($this->orderMock);
        $this->orderStateResolverMock->expects($this->once())
            ->method('getStateForOrder')
            ->with($this->orderMock, [OrderStateResolverInterface::IN_PROGRESS])
            ->willReturn(Order::STATE_PROCESSING);
        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);
        $this->configMock->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn('Processing');
        $this->orderMock->expects($this->once())
            ->method('setStatus')
            ->with('Processing')
            ->willReturnSelf();
        $this->shipmentRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->shipmentMock)
            ->willReturn($this->shipmentMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn($this->orderMock);
        if ($notify) {
            $this->notifierInterfaceMock->expects($this->once())
                ->method('notify')
                ->with($this->orderMock, $this->shipmentMock, $this->shipmentCommentCreationMock);
        }
        $this->shipmentMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(2);
        $this->assertEquals(
            2,
            $this->model->execute(
                $orderId,
                $items,
                $notify,
                $appendComment,
                $this->shipmentCommentCreationMock,
                [$this->trackMock],
                [$this->packageMock],
                $this->shipmentCreationArgumentsMock
            )
        );
    }

    /**
     * @expectedException \Magento\Sales\Api\Exception\DocumentValidationExceptionInterface
     */
    public function testDocumentValidationException()
    {
        $orderId = 1;
        $items = [1 => 2];
        $notify = true;
        $appendComment = true;
        $errorMessages = ['error1', 'error2'];

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->shipmentDocumentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items,
                [$this->trackMock],
                $this->shipmentCommentCreationMock,
                ($appendComment && $notify),
                [$this->packageMock],
                $this->shipmentCreationArgumentsMock
            )->willReturn($this->shipmentMock);

        $this->shipOrderValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->orderMock,
                $this->shipmentMock,
                $items,
                $notify,
                $appendComment,
                $this->shipmentCommentCreationMock,
                [$this->trackMock],
                [$this->packageMock]
            )
            ->willReturn($this->validationMessagesMock);
        $hasMessages = true;
        $this->validationMessagesMock->expects($this->once())
            ->method('hasMessages')->willReturn($hasMessages);
        $this->validationMessagesMock->expects($this->once())
            ->method('getMessages')->willReturn($errorMessages);

        $this->model->execute(
            $orderId,
            $items,
            $notify,
            $appendComment,
            $this->shipmentCommentCreationMock,
            [$this->trackMock],
            [$this->packageMock],
            $this->shipmentCreationArgumentsMock
        );
    }

    /**
     * @expectedException \Magento\Sales\Api\Exception\CouldNotShipExceptionInterface
     */
    public function testCouldNotShipException()
    {
        $orderId = 1;
        $items = [1 => 2];
        $notify = true;
        $appendComment = true;
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterMock);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->shipmentDocumentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items,
                [$this->trackMock],
                $this->shipmentCommentCreationMock,
                ($appendComment && $notify),
                [$this->packageMock],
                $this->shipmentCreationArgumentsMock
            )->willReturn($this->shipmentMock);
        $this->shipOrderValidatorMock->expects($this->once())
            ->method('validate')
            ->with(
                $this->orderMock,
                $this->shipmentMock,
                $items,
                $notify,
                $appendComment,
                $this->shipmentCommentCreationMock,
                [$this->trackMock],
                [$this->packageMock]
            )
            ->willReturn($this->validationMessagesMock);
        $hasMessages = false;
        $this->validationMessagesMock->expects($this->once())
            ->method('hasMessages')->willReturn($hasMessages);
        $exception = new \Exception();

        $this->orderRegistrarMock->expects($this->once())
            ->method('register')
            ->with($this->orderMock, $this->shipmentMock)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->adapterMock->expects($this->once())
            ->method('rollBack');

        $this->model->execute(
            $orderId,
            $items,
            $notify,
            $appendComment,
            $this->shipmentCommentCreationMock,
            [$this->trackMock],
            [$this->packageMock],
            $this->shipmentCreationArgumentsMock
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'TestWithNotifyTrue' => [1, [1 => 2], true, true],
            'TestWithNotifyFalse' => [1, [1 => 2], false, true],
        ];
    }
}
