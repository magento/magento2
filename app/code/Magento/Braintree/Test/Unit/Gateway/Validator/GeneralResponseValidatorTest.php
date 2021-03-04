<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Validator;

use Braintree\Result\Error;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Validator\ErrorCodeProvider;
use Magento\Braintree\Gateway\Validator\GeneralResponseValidator;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Validator\Result;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Class GeneralResponseValidatorTest
 */
class GeneralResponseValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GeneralResponseValidator
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
    protected function setUp(): void
    {
        $this->resultInterfaceFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->responseValidator = new GeneralResponseValidator(
            $this->resultInterfaceFactory,
            new SubjectReader(),
            new ErrorCodeProvider()
        );
    }

    /**
     * Checks a case when the validator processes successful and failed transactions.
     *
     * @param array $validationSubject
     * @param bool $isValid
     * @param Phrase[] $messages
     * @param array $errorCodes
     * @return void
     *
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate(array $validationSubject, bool $isValid, $messages, array $errorCodes)
    {
        $result = new Result($isValid, $messages);

        $this->resultInterfaceFactory->method('create')
            ->with(
                [
                    'isValid' => $isValid,
                    'failsDescription' => $messages,
                    'errorCodes' => $errorCodes
                ]
            )
            ->willReturn($result);

        $actual = $this->responseValidator->validate($validationSubject);

        self::assertEquals($result, $actual);
    }

    /**
     * Gets variations for different type of response.
     *
     * @return array
     */
    public function dataProviderTestValidate()
    {
        $successTransaction = new \stdClass();
        $successTransaction->success = true;
        $successTransaction->status = 'authorized';

        $failureTransaction = new \stdClass();
        $failureTransaction->success = false;
        $failureTransaction->status = 'declined';
        $failureTransaction->message = 'Transaction was failed.';

        $errors = [
            'errors' => [
                [
                    'code' => 81804,
                    'attribute' => 'base',
                    'message' => 'Cannot process transaction.'
                ],
            ]
        ];
        $errorTransaction = new Error(['errors' => $errors, 'transaction' => ['status' => 'declined']]);

        return [
            [
                'validationSubject' => [
                    'response' => [
                        'object' => $successTransaction
                    ],
                ],
                'isValid' => true,
                [],
                'errorCodes' => []
            ],
            [
                'validationSubject' => [
                    'response' => [
                        'object' => $failureTransaction
                    ]
                ],
                'isValid' => false,
                [
                    __('Transaction was failed.')
                ],
                'errorCodes' => []
            ],
            [
                'validationSubject' => [
                    'response' => [
                        'object' => $errorTransaction
                    ]
                ],
                'isValid' => false,
                [
                    __('Braintree error response.')
                ],
                'errorCodes' => ['81804']
            ]
        ];
    }
}
