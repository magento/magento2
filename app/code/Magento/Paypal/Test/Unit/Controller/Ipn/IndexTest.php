<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Controller\Ipn;

use Magento\Framework\Event\ManagerInterface;
use Magento\Paypal\Controller\Ipn\Index;
use Magento\Paypal\Model\IpnFactory;
use Magento\Paypal\Model\IpnInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /** @var Index */
    private $model;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $loggerMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $requestMock;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $responseMock;

    /**
     * @var IpnFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ipnFactoryMock;

    /**
     * @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->ipnFactoryMock = $this->createMock(IpnFactory::class);
        $this->orderFactoryMock = $this->createMock(OrderFactory::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $this->requestMock->expects($this->once())->method('getPostValue')->will($this->throwException($exception));
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
