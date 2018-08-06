<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Controller\Payment;

use Magento\Braintree\Controller\Payment\GetNonce;
use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Exception;
use Magento\Payment\Gateway\Command\ResultInterface as CommandResultInterface;
use Psr\Log\LoggerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class GetNonceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetNonceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetNonce
     */
    private $action;

    /**
     * @var GetPaymentNonceCommand|MockObject
     */
    private $command;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactory;

    /**
     * @var ResultInterface|MockObject
     */
    private $result;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var CommandResultInterface|MockObject
     */
    private $commandResult;

    protected function setUp()
    {
        $this->initResultFactoryMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->command = $this->getMockBuilder(GetPaymentNonceCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute', '__wakeup'])
            ->getMock();

        $this->commandResult = $this->getMockBuilder(CommandResultInterface::class)
            ->setMethods(['get'])
            ->getMock();

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getStoreId'])
            ->getMock();

        $this->logger = $this->createMock(LoggerInterface::class);

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->method('getRequest')
            ->willReturn($this->request);
        $context->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $managerHelper = new ObjectManager($this);
        $this->action = $managerHelper->getObject(GetNonce::class, [
            'context' => $context,
            'logger' => $this->logger,
            'session' => $this->session,
            'command' => $this->command
        ]);
    }

    public function testExecuteWithException()
    {
        $this->request->method('getParam')
            ->with('public_hash')
            ->willReturn(null);

        $this->session->method('getCustomerId')
            ->willReturn(null);
        $this->session->method('getStoreId')
            ->willReturn(null);

        $exception = new \Exception('The "publicHash" field does not exists');
        $this->command->method('execute')
            ->willThrowException($exception);

        $this->logger->method('critical')
            ->with($exception);

        $this->result->method('setHttpResponseCode')
            ->with(Exception::HTTP_BAD_REQUEST);
        $this->result->method('setData')
            ->with(['message' => 'Sorry, but something went wrong']);

        $this->action->execute();
    }

    public function testExecute()
    {
        $customerId = 1;
        $publicHash = '65b7bae0dcb690d93';
        $nonce = 'f1hc45';

        $this->request->method('getParam')
            ->with('public_hash')
            ->willReturn($publicHash);

        $this->session->method('getCustomerId')
            ->willReturn($customerId);
        $this->session->method('getStoreId')
            ->willReturn(null);

        $this->commandResult->method('get')
            ->willReturn([
                'paymentMethodNonce' => $nonce
            ]);
        $this->command->method('execute')
            ->willReturn($this->commandResult);

        $this->result->method('setData')
            ->with(['paymentMethodNonce' => $nonce]);

        $this->logger->expects(self::never())
            ->method('critical');

        $this->result->expects(self::never())
            ->method('setHttpResponseCode');

        $this->action->execute();
    }

    /**
     * Creates mock for result factory object
     */
    private function initResultFactoryMock()
    {
        $this->result = $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['setHttpResponseCode', 'renderResult', 'setHeader', 'setData'])
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultFactory->method('create')
            ->willReturn($this->result);
    }
}
