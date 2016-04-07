<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Command;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Validator\PaymentNonceResponseValidator;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenManagement;

/**
 * Class GetPaymentNonceCommandTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetPaymentNonceCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GetPaymentNonceCommand
     */
    private $command;

    /**
     * @var BraintreeAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var PaymentTokenManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenManagement;

    /**
     * @var PaymentToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentToken;

    /**
     * @var ArrayResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReader;

    /**
     * @var PaymentNonceResponseValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseValidator;

    /**
     * @var ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationResult;

    protected function setUp()
    {
        $this->paymentToken = $this->getMockBuilder(PaymentToken::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGatewayToken'])
            ->getMock();

        $this->tokenManagement = $this->getMockBuilder(PaymentTokenManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByPublicHash'])
            ->getMock();

        $this->adapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['createNonce'])
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(ArrayResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readPublicHash', 'readCustomerId'])
            ->getMock();

        $this->validationResult = $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['isValid', 'getFailsDescription'])
            ->getMock();

        $this->responseValidator = $this->getMockBuilder(PaymentNonceResponseValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate', 'isValid', 'getFailsDescription'])
            ->getMock();

        $this->command = new GetPaymentNonceCommand(
            $this->tokenManagement,
            $this->adapter,
            $this->resultFactory,
            $this->subjectReader,
            $this->responseValidator
        );
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\GetPaymentNonceCommand::execute
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "publicHash" field does not exists
     */
    public function testExecuteWithExceptionForPublicHash()
    {
        $exception = new \InvalidArgumentException('The "publicHash" field does not exists');

        $this->subjectReader->expects(static::once())
            ->method('readPublicHash')
            ->willThrowException($exception);

        $this->subjectReader->expects(static::never())
            ->method('readCustomerId');

        $this->command->execute([]);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\GetPaymentNonceCommand::execute
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "customerId" field does not exists
     */
    public function testExecuteWithExceptionForCustomerId()
    {
        $publicHash = '3wv2m24d2er3';

        $this->subjectReader->expects(static::once())
            ->method('readPublicHash')
            ->willReturn($publicHash);

        $exception = new \InvalidArgumentException('The "customerId" field does not exists');
        $this->subjectReader->expects(static::once())
            ->method('readCustomerId')
            ->willThrowException($exception);

        $this->tokenManagement->expects(static::never())
            ->method('getByPublicHash');

        $this->command->execute(['publicHash' => $publicHash]);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\GetPaymentNonceCommand::execute
     * @expectedException \Exception
     * @expectedExceptionMessage No available payment tokens
     */
    public function testExecuteWithExceptionForTokenManagement()
    {
        $publicHash = '3wv2m24d2er3';
        $customerId = 1;

        $this->subjectReader->expects(static::once())
            ->method('readPublicHash')
            ->willReturn($publicHash);

        $this->subjectReader->expects(static::once())
            ->method('readCustomerId')
            ->willReturn($customerId);

        $exception = new \Exception('No available payment tokens');
        $this->tokenManagement->expects(static::once())
            ->method('getByPublicHash')
            ->willThrowException($exception);

        $this->paymentToken->expects(static::never())
            ->method('getGatewayToken');

        $this->command->execute(['publicHash' => $publicHash, 'customerId' => $customerId]);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\GetPaymentNonceCommand::execute
     * @expectedException \Exception
     * @expectedExceptionMessage Payment method nonce can't be retrieved.
     */
    public function testExecuteWithFailedValidation()
    {
        $publicHash = '3wv2m24d2er3';
        $customerId = 1;
        $token = 'jd2vnq';

        $this->subjectReader->expects(static::once())
            ->method('readPublicHash')
            ->willReturn($publicHash);

        $this->subjectReader->expects(static::once())
            ->method('readCustomerId')
            ->willReturn($customerId);

        $this->tokenManagement->expects(static::once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($this->paymentToken);

        $this->paymentToken->expects(static::once())
            ->method('getGatewayToken')
            ->willReturn($token);

        $obj = new \stdClass();
        $obj->success = false;
        $this->adapter->expects(static::once())
            ->method('createNonce')
            ->with($token)
            ->willReturn($obj);

        $this->responseValidator->expects(static::once())
            ->method('validate')
            ->with(['response' => ['object' => $obj]])
            ->willReturn($this->validationResult);

        $this->validationResult->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $this->validationResult->expects(static::once())
            ->method('getFailsDescription')
            ->willReturn(['Payment method nonce can\'t be retrieved.']);

        $this->resultFactory->expects(static::never())
            ->method('create');

        $this->command->execute(['publicHash' => $publicHash, 'customerId' => $customerId]);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\GetPaymentNonceCommand::execute
     */
    public function testExecute()
    {
        $publicHash = '3wv2m24d2er3';
        $customerId = 1;
        $token = 'jd2vnq';
        $nonce = 's1dj23';

        $this->subjectReader->expects(static::once())
            ->method('readPublicHash')
            ->willReturn($publicHash);

        $this->subjectReader->expects(static::once())
            ->method('readCustomerId')
            ->willReturn($customerId);

        $this->tokenManagement->expects(static::once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($this->paymentToken);

        $this->paymentToken->expects(static::once())
            ->method('getGatewayToken')
            ->willReturn($token);

        $obj = new \stdClass();
        $obj->success = true;
        $obj->paymentMethodNonce = new \stdClass();
        $obj->paymentMethodNonce->nonce = $nonce;
        $this->adapter->expects(static::once())
            ->method('createNonce')
            ->with($token)
            ->willReturn($obj);

        $this->responseValidator->expects(static::once())
            ->method('validate')
            ->with(['response' => ['object' => $obj]])
            ->willReturn($this->validationResult);

        $this->validationResult->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->validationResult->expects(static::never())
            ->method('getFailsDescription');

        $expected = $this->getMockBuilder(ArrayResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $expected->expects(static::once())
            ->method('get')
            ->willReturn(['paymentMethodNonce' => $nonce]);
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->willReturn($expected);

        $actual = $this->command->execute(['publicHash' => $publicHash, 'customerId' => $customerId]);
        static::assertEquals($expected, $actual);
        static::assertEquals($nonce, $actual->get()['paymentMethodNonce']);
    }
}
