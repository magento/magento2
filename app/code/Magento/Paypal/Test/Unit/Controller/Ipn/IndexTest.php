<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Controller\Ipn;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Controller\Ipn\Index;
use Magento\Paypal\Model\IpnFactory;
use Magento\Paypal\Model\IpnInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /** @var Index */
    private $model;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    /** @var RequestInterface|MockObject */
    private $requestMock;

    /** @var ResponseInterface|MockObject */
    private $responseMock;

    /**
     * @var IpnFactory|MockObject
     */
    private $ipnFactoryMock;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->ipnFactoryMock = $this->createMock(IpnFactory::class);
        $this->orderFactoryMock = $this->createMock(OrderFactory::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Index::class,
            [
                'logger' => $this->loggerMock,
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'ipnFactory' => $this->ipnFactoryMock,
                'orderFactory' => $this->orderFactoryMock,
                'eventManager' => $this->eventManagerMock
            ]
        );
    }

    public function testIndexActionException()
    {
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
        $exception = new \Exception();
        $this->requestMock->expects($this->once())->method('getPostValue')->willThrowException($exception);
        $this->loggerMock->expects($this->once())->method('critical')->with($this->identicalTo($exception));
        $this->responseMock->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->model->execute();
    }

    public function testIndexAction()
    {
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
        $incrementId = 'incrementId';
        $data = [
            'invoice' => $incrementId,
            'other' => 'other data'
        ];
        $this->requestMock->expects($this->exactly(2))->method('getPostValue')->willReturn($data);
        $ipnMock = $this->getMockForAbstractClass(IpnInterface::class);
        $this->ipnFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $data])
            ->willReturn($ipnMock);
        $ipnMock->expects($this->once())
            ->method('processIpnRequest');
        $orderMock = $this->createMock(Order::class);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($incrementId)
            ->willReturn($orderMock);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('paypal_checkout_success', ['order' => $orderMock]);
        $this->model->execute();
    }
}
