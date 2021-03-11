<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Controller\Transparent;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic as Session;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Paypal\Controller\Transparent\Response;
use Magento\Paypal\Model\Payflow\Service\Response\Transaction;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Sales\Api\PaymentFailuresInterface;

/**
 * Test for class \Magento\Paypal\Controller\Transparent\Response
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /** @var Response|\PHPUnit\Framework\MockObject\MockObject */
    private $object;

    /** @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $requestMock;

    /** @var Registry|\PHPUnit\Framework\MockObject\MockObject */
    private $coreRegistryMock;

    /** @var LayoutFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $resultLayoutFactoryMock;

    /** @var Layout|\PHPUnit\Framework\MockObject\MockObject */
    private $resultLayoutMock;

    /** @var Context|\PHPUnit\Framework\MockObject\MockObject */
    private $contextMock;

    /** @var Transaction|\PHPUnit\Framework\MockObject\MockObject */
    private $transactionMock;

    /** @var ResponseValidator|\PHPUnit\Framework\MockObject\MockObject */
    private $responseValidatorMock;

    /**
     * @var Transparent | \PHPUnit\Framework\MockObject\MockObject
     */
    private $payflowFacade;

    /**
     * @var PaymentFailuresInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentFailures;

    /**
     * @var Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionTransparent;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->coreRegistryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->setMethods(['register'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultLayoutMock = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->setMethods(['addDefaultHandle', 'getLayout', 'getUpdate', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultLayoutFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\LayoutFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultLayoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultLayoutMock);
        $this->transactionMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Payflow\Service\Response\Transaction::class
        )->setMethods(['getResponseObject', 'validateResponse', 'savePaymentInQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->setMethods(['getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->responseValidatorMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->payflowFacade = $this->getMockBuilder(Transparent::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->paymentFailures = $this->getMockBuilder(PaymentFailuresInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['handle'])
            ->getMockForAbstractClass();
        $this->sessionTransparent = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteId'])
            ->getMock();

        $this->object = new Response(
            $this->contextMock,
            $this->coreRegistryMock,
            $this->transactionMock,
            $this->responseValidatorMock,
            $this->resultLayoutFactoryMock,
            $this->payflowFacade,
            $this->sessionTransparent,
            $this->paymentFailures
        );
    }

    public function testExecute()
    {
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionMock->expects($this->once())
            ->method('getResponseObject')
            ->willReturn($objectMock);
        $this->responseValidatorMock->expects($this->once())
            ->method('validate')
            ->with($objectMock, $this->payflowFacade);
        $this->transactionMock->expects($this->once())
            ->method('savePaymentInQuote')
            ->with($objectMock);
        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with('transparent_form_params', $this->logicalNot($this->arrayHasKey('error')));
        $this->resultLayoutMock->expects($this->once())
            ->method('addDefaultHandle')
            ->willReturnSelf();
        $this->resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->getLayoutMock());
        $this->paymentFailures->expects($this->never())
            ->method('handle');

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->object->execute());
    }

    public function testExecuteWithException()
    {
        $objectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionMock->expects($this->once())
            ->method('getResponseObject')
            ->willReturn($objectMock);
        $this->responseValidatorMock->expects($this->once())
            ->method('validate')
            ->with($objectMock, $this->payflowFacade)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('Error')));
        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with('transparent_form_params', $this->arrayHasKey('error'));
        $this->resultLayoutMock->expects($this->once())
            ->method('addDefaultHandle')
            ->willReturnSelf();
        $this->resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->getLayoutMock());
        $this->sessionTransparent->method('getQuoteId')
            ->willReturn(1);
        $this->paymentFailures->expects($this->once())
            ->method('handle')
            ->with(1)
            ->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->object->execute());
    }

    /**
     * @return \Magento\Framework\View\Layout | \PHPUnit\Framework\MockObject\MockObject
     */
    private function getLayoutMock()
    {
        $processorInterfaceMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ProcessorInterface::class)
            ->getMockForAbstractClass();
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->setMethods(['getUpdate'])
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('getUpdate')
            ->willReturn($processorInterfaceMock);

        return $layoutMock;
    }
}
