<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Validator;

use Braintree\Transaction;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Braintree\Gateway\Validator\ResponseValidator;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Braintree\Result\Error;
use Braintree\Result\Successful;

/**
 * Class ResponseValidatorTest
 */
class ResponseValidatorTest extends \PHPUnit_Framework_TestCase
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
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

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
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseValidator = new ResponseValidator(
            $this->resultInterfaceFactory,
            $this->subjectReader
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

        $this->subjectReader->expects(self::once())
            ->method('readResponseObject')
            ->with($validationSubject)
            ->willThrowException(new \InvalidArgumentException());

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

        $this->subjectReader->expects(self::once())
            ->method('readResponseObject')
            ->with($validationSubject)
            ->willThrowException(new \InvalidArgumentException());

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
        $result = $this->getMock(ResultInterface::class);

        $this->subjectReader->expects(self::once())
            ->method('readResponseObject')
            ->with($validationSubject)
            ->willReturn($validationSubject['response']['object']);

        $this->resultInterfaceFactory->expects(self::once())
            ->method('create')
            ->with([
                'isValid' => $isValid,
                'failsDescription' => $messages
            ])
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

        $errorResult = new Error(['errors' => []]);

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
            ],
            [
                'validationSubject' => [
                    'response' => [
                        'object' => $errorResult,
                    ]
                ],
                'isValid' => false,
                [
                    __('Braintree error response.'),
                    __('Wrong transaction status')
                ]
            ]
        ];
    }
}
