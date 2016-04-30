<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Validator;

use Braintree\Transaction;
use Magento\Braintree\Gateway\Validator\PaymentNonceResponseValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Braintree\Gateway\Helper\SubjectReader;

/**
 * Class PaymentNonceResponseValidatorTest
 */
class PaymentNonceResponseValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentNonceResponseValidator
     */
    private $validator;

    /**
     * @var ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultInterfaceFactory;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReader;

    protected function setUp()
    {
        $this->resultInterfaceFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readResponseObject'])
            ->getMock();

        $this->validator = new PaymentNonceResponseValidator(
            $this->resultInterfaceFactory,
            $this->subjectReader
        );
    }

    /**
     * @covers \Magento\Braintree\Gateway\Validator\PaymentNonceResponseValidator::validate
     */
    public function testFailedValidate()
    {
        $obj = new \stdClass();
        $obj->success = true;
        $subject = [
            'response' => [
                'object' => $obj
            ]
        ];

        $this->subjectReader->expects(static::once())
            ->method('readResponseObject')
            ->willReturn($obj);

        $result = $this->getMock(ResultInterface::class);
        $this->resultInterfaceFactory->expects(self::once())
            ->method('create')
            ->with([
                'isValid' => false,
                'failsDescription' => [
                    __('Payment method nonce can\'t be retrieved.')
                ]
            ])
            ->willReturn($result);

        $actual = $this->validator->validate($subject);
        static::assertEquals($result, $actual);
    }

    public function testValidateSuccess()
    {
        $obj = new \stdClass();
        $obj->success = true;
        $obj->paymentMethodNonce = new \stdClass();
        $obj->paymentMethodNonce->nonce = 'fj2hd9239kd1kq9';

        $subject = [
            'response' => [
                'object' => $obj
            ]
        ];

        $this->subjectReader->expects(static::once())
            ->method('readResponseObject')
            ->willReturn($obj);

        $result = $this->getMock(ResultInterface::class);
        $this->resultInterfaceFactory->expects(self::once())
            ->method('create')
            ->with([
                'isValid' => true,
                'failsDescription' => []
            ])
            ->willReturn($result);

        $actual = $this->validator->validate($subject);
        static::assertEquals($result, $actual);
    }
}
