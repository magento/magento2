<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Validator;

use Braintree\Transaction;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Braintree\Gateway\Validator\GeneralResponseValidator;
use Magento\Braintree\Gateway\Helper\SubjectReader;

class GeneralResponseValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GeneralResponseValidator
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
            \Magento\Payment\Gateway\Validator\ResultInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseValidator = new GeneralResponseValidator(
            $this->resultInterfaceFactoryMock,
            $this->subjectReaderMock
        );
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
        /** @var ResultInterface|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->createMock(ResultInterface::class);

        $this->subjectReaderMock->expects(self::once())
            ->method('readResponseObject')
            ->with($validationSubject)
            ->willReturn($validationSubject['response']['object']);

        $this->resultInterfaceFactoryMock->expects(self::once())
            ->method('create')
            ->with([
                'isValid' => $isValid,
                'failsDescription' => $messages
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

        $successFalse = new \stdClass();
        $successFalse->success = false;

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
                    __('Braintree error response.')
                ]
            ]
        ];
    }
}
