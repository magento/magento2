<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Validator;

use Braintree\Transaction;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\BraintreeTwo\Gateway\Validator\ResponseValidator;
use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;

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
     * @var ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultInterfaceFactoryMock;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resultInterfaceFactoryMock = $this->getMockBuilder(
            'Magento\Payment\Gateway\Validator\ResultInterfaceFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseValidator = new ResponseValidator(
            $this->resultInterfaceFactoryMock,
            $this->subjectReaderMock
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

        $this->subjectReaderMock->expects(self::once())
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

        $this->subjectReaderMock->expects(self::once())
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
     * @return void
     *
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate(array $validationSubject, $isValid)
    {
        /** @var ResultInterface|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMock(ResultInterface::class);

        $this->subjectReaderMock->expects(self::once())
            ->method('readResponseObject')
            ->with($validationSubject)
            ->willReturn($validationSubject['response']['object']);

        $this->resultInterfaceFactoryMock->expects(self::once())
            ->method('create')
            ->with([
                'isValid' => $isValid,
                'failsDescription' => ['Transaction has been declined. Please try again later.']
            ])
            ->willReturn($resultMock);

        $actualMock = $this->responseValidator->validate($validationSubject);

        self::assertEquals($resultMock, $actualMock);
    }

    /**
     * @return array
     */
    public function dataProviderTestValidate()
    {
        $successTrue = new \stdClass();
        $successTrue->success = true;
        $successTrue->transaction = new \stdClass();
        $successTrue->transaction->status = Transaction::AUTHORIZED;

        $successFalse = new \stdClass();
        $successFalse->success = false;

        $transactionDeclined = new \stdClass();
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
            ],
            [
                'validationSubject' => [
                    'response' => [
                        'object' => $successFalse
                    ]
                ],
                'isValid' => false
            ],
            [
                'validationSubject' => [
                    'response' => [
                        'object' => $transactionDeclined
                    ]
                ],
                'isValid' => false
            ]
        ];
    }
}
