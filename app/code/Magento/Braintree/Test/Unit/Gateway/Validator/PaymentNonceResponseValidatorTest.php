<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Validator;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Validator\ErrorCodeProvider;
use Magento\Braintree\Gateway\Validator\PaymentNonceResponseValidator;
use Magento\Payment\Gateway\Validator\Result;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

class PaymentNonceResponseValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentNonceResponseValidator
     */
    private $validator;

    /**
     * @var ResultInterfaceFactory|MockObject
     */
    private $resultInterfaceFactory;

    protected function setUp(): void
    {
        $this->resultInterfaceFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->validator = new PaymentNonceResponseValidator(
            $this->resultInterfaceFactory,
            new SubjectReader(),
            new ErrorCodeProvider()
        );
    }

    public function testFailedValidate()
    {
        $obj = new \stdClass();
        $obj->success = true;
        $subject = [
            'response' => [
                'object' => $obj
            ]
        ];

        $result = new Result(false, [__('Payment method nonce can\'t be retrieved.')]);
        $this->resultInterfaceFactory->method('create')
            ->willReturn($result);

        $actual = $this->validator->validate($subject);
        self::assertEquals($result, $actual);
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

        $result = new Result(true);
        $this->resultInterfaceFactory->method('create')
            ->willReturn($result);

        $actual = $this->validator->validate($subject);
        self::assertEquals($result, $actual);
    }
}
