<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Command;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Validator\PaymentNonceResponseValidator;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenManagement;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class GetPaymentNonceCommandTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetPaymentNonceCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetPaymentNonceCommand
     */
    private $command;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $adapter;

    /**
     * @var PaymentTokenManagement|MockObject
     */
    private $tokenManagement;

    /**
     * @var PaymentToken|MockObject
     */
    private $paymentToken;

    /**
     * @var ArrayResultFactory|MockObject
     */
    private $resultFactory;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    /**
     * @var PaymentNonceResponseValidator|MockObject
     */
    private $responseValidator;

    /**
     * @var ResultInterface|MockObject
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
        /** @var BraintreeAdapterFactory|MockObject $adapterFactory */
        $adapterFactory = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactory->method('create')
            ->willReturn($this->adapter);

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
            $adapterFactory,
            $this->resultFactory,
            $this->subjectReader,
            $this->responseValidator
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "publicHash" field does not exists
     */
    public function testExecuteWithExceptionForPublicHash()
    {
        $exception = new \InvalidArgumentException('The "publicHash" field does not exists');

        $this->subjectReader->method('readPublicHash')
            ->willThrowException($exception);

        $this->subjectReader->expects(self::never())
            ->method('readCustomerId');

        $this->command->execute([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "customerId" field does not exists
     */
    public function testExecuteWithExceptionForCustomerId()
    {
        $publicHash = '3wv2m24d2er3';

        $this->subjectReader->method('readPublicHash')
            ->willReturn($publicHash);

        $exception = new \InvalidArgumentException('The "customerId" field does not exists');
        $this->subjectReader->method('readCustomerId')
            ->willThrowException($exception);

        $this->tokenManagement->expects(self::never())
            ->method('getByPublicHash');

        $this->command->execute(['publicHash' => $publicHash]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No available payment tokens
     */
    public function testExecuteWithExceptionForTokenManagement()
    {
        $publicHash = '3wv2m24d2er3';
        $customerId = 1;

        $this->subjectReader->method('readPublicHash')
            ->willReturn($publicHash);

        $this->subjectReader->method('readCustomerId')
            ->willReturn($customerId);

        $exception = new \Exception('No available payment tokens');
        $this->tokenManagement->method('getByPublicHash')
            ->willThrowException($exception);

        $this->paymentToken->expects(self::never())
            ->method('getGatewayToken');

        $this->command->execute(['publicHash' => $publicHash, 'customerId' => $customerId]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Payment method nonce can't be retrieved.
     */
    public function testExecuteWithFailedValidation()
    {
        $publicHash = '3wv2m24d2er3';
        $customerId = 1;
        $token = 'jd2vnq';

        $this->subjectReader->method('readPublicHash')
            ->willReturn($publicHash);

        $this->subjectReader->method('readCustomerId')
            ->willReturn($customerId);

        $this->tokenManagement->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($this->paymentToken);

        $this->paymentToken->method('getGatewayToken')
            ->willReturn($token);

        $obj = new \stdClass();
        $obj->success = false;
        $this->adapter->method('createNonce')
            ->with($token)
            ->willReturn($obj);

        $this->responseValidator->method('validate')
            ->with(['response' => ['object' => $obj]])
            ->willReturn($this->validationResult);

        $this->validationResult->method('isValid')
            ->willReturn(false);

        $this->validationResult->method('getFailsDescription')
            ->willReturn(['Payment method nonce can\'t be retrieved.']);

        $this->resultFactory->expects(self::never())
            ->method('create');

        $this->command->execute(['publicHash' => $publicHash, 'customerId' => $customerId]);
    }

    public function testExecute()
    {
        $publicHash = '3wv2m24d2er3';
        $customerId = 1;
        $token = 'jd2vnq';
        $nonce = 's1dj23';

        $this->subjectReader->method('readPublicHash')
            ->willReturn($publicHash);

        $this->subjectReader->method('readCustomerId')
            ->willReturn($customerId);

        $this->tokenManagement->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($this->paymentToken);

        $this->paymentToken->method('getGatewayToken')
            ->willReturn($token);

        $obj = new \stdClass();
        $obj->success = true;
        $obj->paymentMethodNonce = new \stdClass();
        $obj->paymentMethodNonce->nonce = $nonce;
        $this->adapter->method('createNonce')
            ->with($token)
            ->willReturn($obj);

        $this->responseValidator->method('validate')
            ->with(['response' => ['object' => $obj]])
            ->willReturn($this->validationResult);

        $this->validationResult->method('isValid')
            ->willReturn(true);

        $this->validationResult->expects(self::never())
            ->method('getFailsDescription');

        $expected = $this->getMockBuilder(ArrayResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $expected->method('get')
            ->willReturn(['paymentMethodNonce' => $nonce]);
        $this->resultFactory->method('create')
            ->willReturn($expected);

        $actual = $this->command->execute(['publicHash' => $publicHash, 'customerId' => $customerId]);
        self::assertEquals($expected, $actual);
        self::assertEquals($nonce, $actual->get()['paymentMethodNonce']);
    }
}
