<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Validator;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Validator\ErrorCodeProvider;
use Magento\Braintree\Gateway\Validator\ResponseValidator;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Validator\Result;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ResponseValidatorTest
 */
class ResponseValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResponseValidator
     */
    private $responseValidator;

    /**
     * @var ResultInterfaceFactory|MockObject
     */
    private $resultInterfaceFactory;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resultInterfaceFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->responseValidator = new ResponseValidator(
            $this->resultInterfaceFactory,
            new SubjectReader(),
            new ErrorCodeProvider()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateReadResponseException()
    {
        $validationSubject = [
            'response' => null
        ];

        $this->responseValidator->validate($validationSubject);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateReadResponseObjectException()
    {
        $validationSubject = [
            'response' => ['object' => null]
        ];

        $this->responseValidator->validate($validationSubject);
    }

    /**
     * Run test for validate method
     *
     * @param array $validationSubject
     * @param bool $isValid
     * @param Phrase[] $messages
     * @return void
     *
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate(array $validationSubject, $isValid, $messages)
    {
        /** @var ResultInterface|MockObject $result */
        $result = new Result($isValid, $messages);

        $this->resultInterfaceFactory->method('create')
            ->willReturn($result);

        $actual = $this->responseValidator->validate($validationSubject);

        self::assertEquals($result, $actual);
    }

    /**
     * @return array
     */
    public function dataProviderTestValidate()
    {
        $successTrue = new Successful();
        $successTrue->success = true;
        $successTrue->transaction = new \stdClass();
        $successTrue->transaction->status = Transaction::AUTHORIZED;

        $successFalse = new Successful();
        $successFalse->success = false;

        $transactionDeclined = new Successful();
        $transactionDeclined->success = true;
        $transactionDeclined->transaction = new \stdClass();
        $transactionDeclined->transaction->status = Transaction::SETTLEMENT_DECLINED;

        return [
            [
                'validationSubject' => [
                    'response' => [
                        'object' => $successTrue
                    ],
                ],
                'isValid' => true,
                []
            ],
            [
                'validationSubject' => [
                    'response' => [
                        'object' => $successFalse
                    ]
                ],
                'isValid' => false,
                [
                    __('Braintree error response.'),
                    __('Wrong transaction status')
                ]
            ],
            [
                'validationSubject' => [
                    'response' => [
                        'object' => $transactionDeclined
                    ]
                ],
                'isValid' => false,
                [
                    __('Wrong transaction status')
                ]
            ]
        ];
    }
}
