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
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Test for GetPaymentNonceCommand
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
    private $adapterMock;

    /**
     * @var PaymentTokenManagement|MockObject
     */
    private $tokenManagementMock;

    /**
     * @var PaymentToken|MockObject
     */
    private $paymentTokenMock;

    /**
     * @var ArrayResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var PaymentNonceResponseValidator|MockObject
     */
    private $responseValidatorMock;

    /**
     * @var ResultInterface|MockObject
     */
    private $validationResultMock;

    protected function setUp(): void
    {
        $this->paymentTokenMock = $this->getMockBuilder(PaymentToken::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGatewayToken'])
            ->getMock();

        $this->tokenManagementMock = $this->getMockBuilder(PaymentTokenManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByPublicHash'])
            ->getMock();

        $this->adapterMock = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['createNonce'])
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactoryMock */
        $adapterFactoryMock = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactoryMock->expects(self::any())
            ->method('create')
            ->willReturn($this->adapterMock);

        $this->resultFactoryMock = $this->getMockBuilder(ArrayResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readPublicHash', 'readCustomerId'])
            ->getMock();

        $this->validationResultMock = $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['isValid', 'getFailsDescription', 'getErrorCodes'])
            ->getMockForAbstractClass();

        $this->responseValidatorMock = $this->getMockBuilder(PaymentNonceResponseValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate', 'isValid', 'getFailsDescription'])
            ->getMock();

        $this->command = new GetPaymentNonceCommand(
            $this->tokenManagementMock,
            $adapterFactoryMock,
            $this->resultFactoryMock,
            $this->subjectReaderMock,
            $this->responseValidatorMock
        );
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\GetPaymentNonceCommand::execute
     */
    public function testExecuteWithExceptionForPublicHash()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "publicHash" field does not exists');

        $exception = new \InvalidArgumentException('The "publicHash" field does not exists');

        $this->subjectReaderMock->expects(static::once())
            ->method('readPublicHash')
            ->willThrowException($exception);

        $this->subjectReaderMock->expects(self::never())
            ->method('readCustomerId');

        $this->command->execute([]);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\GetPaymentNonceCommand::execute
     */
    public function testExecuteWithExceptionForCustomerId()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "customerId" field does not exists');

        $publicHash = '3wv2m24d2er3';

        $this->subjectReaderMock->expects(static::once())
            ->method('readPublicHash')
            ->willReturn($publicHash);

        $exception = new \InvalidArgumentException('The "customerId" field does not exists');
        $this->subjectReaderMock->expects(static::once())
            ->method('readCustomerId')
            ->willThrowException($exception);

        $this->tokenManagementMock->expects(static::never())
            ->method('getByPublicHash');

        $this->command->execute(['publicHash' => $publicHash]);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\GetPaymentNonceCommand::execute
     */
    public function testExecuteWithExceptionForTokenManagement()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No available payment tokens');

        $publicHash = '3wv2m24d2er3';
        $customerId = 1;

        $this->subjectReaderMock->expects(static::once())
            ->method('readPublicHash')
            ->willReturn($publicHash);

        $this->subjectReaderMock->expects(static::once())
            ->method('readCustomerId')
            ->willReturn($customerId);

        $exception = new \Exception('No available payment tokens');
        $this->tokenManagementMock->expects(static::once())
            ->method('getByPublicHash')
            ->willThrowException($exception);

        $this->paymentTokenMock->expects(self::never())
            ->method('getGatewayToken');

        $this->command->execute(['publicHash' => $publicHash, 'customerId' => $customerId]);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\GetPaymentNonceCommand::execute
     */
    public function testExecuteWithFailedValidation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment method nonce can\'t be retrieved.');

        $publicHash = '3wv2m24d2er3';
        $customerId = 1;
        $token = 'jd2vnq';

        $this->subjectReaderMock->expects(static::once())
            ->method('readPublicHash')
            ->willReturn($publicHash);

        $this->subjectReaderMock->expects(static::once())
            ->method('readCustomerId')
            ->willReturn($customerId);

        $this->tokenManagementMock->expects(static::once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($this->paymentTokenMock);

        $this->paymentTokenMock->expects(static::once())
            ->method('getGatewayToken')
            ->willReturn($token);

        $obj = new \stdClass();
        $obj->success = false;
        $this->adapterMock->expects(static::once())
            ->method('createNonce')
            ->with($token)
            ->willReturn($obj);

        $this->responseValidatorMock->expects(static::once())
            ->method('validate')
            ->with(['response' => ['object' => $obj]])
            ->willReturn($this->validationResultMock);

        $this->validationResultMock->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $this->validationResultMock->expects(static::once())
            ->method('getFailsDescription')
            ->willReturn(['Payment method nonce can\'t be retrieved.']);

        $this->resultFactoryMock->expects(static::never())
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

        $this->subjectReaderMock->expects(static::once())
            ->method('readPublicHash')
            ->willReturn($publicHash);

        $this->subjectReaderMock->expects(static::once())
            ->method('readCustomerId')
            ->willReturn($customerId);

        $this->tokenManagementMock->expects(static::once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($this->paymentTokenMock);

        $this->paymentTokenMock->expects(static::once())
            ->method('getGatewayToken')
            ->willReturn($token);

        $obj = new \stdClass();
        $obj->success = true;
        $obj->paymentMethodNonce = new \stdClass();
        $obj->paymentMethodNonce->nonce = $nonce;
        $this->adapterMock->expects(static::once())
            ->method('createNonce')
            ->with($token)
            ->willReturn($obj);

        $this->responseValidatorMock->expects(static::once())
            ->method('validate')
            ->with(['response' => ['object' => $obj]])
            ->willReturn($this->validationResultMock);

        $this->validationResultMock->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->validationResultMock->expects(self::never())
            ->method('getFailsDescription');

        $expected = $this->getMockBuilder(ArrayResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $expected->expects(static::once())
            ->method('get')
            ->willReturn(['paymentMethodNonce' => $nonce]);
        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($expected);

        $actual = $this->command->execute(['publicHash' => $publicHash, 'customerId' => $customerId]);
        self::assertEquals($expected, $actual);
        self::assertEquals($nonce, $actual->get()['paymentMethodNonce']);
    }
}
