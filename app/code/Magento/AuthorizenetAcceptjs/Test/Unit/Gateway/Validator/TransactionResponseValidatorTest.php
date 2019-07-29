<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Gateway\Validator\TransactionResponseValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Validator\TransactionResponseValidator
 */
class TransactionResponseValidatorTest extends TestCase
{
    /**
     * @var ResultInterfaceFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var TransactionResponseValidator
     */
    private $validator;

    /**
     * @var ResultInterface
     */
    private $resultMock;

    /**
     * @var int
     */
    private $responseCodeApproved = 1;

    /**
     * @var int
     */
    private $responseCodeHeld = 4;

    /**
     * @var int
     */
    private $responseReasonCodeApproved = 1;

    /**
     * @var int
     */
    private $responseReasonCodePendingReviewAuthorized = 252;

    /**
     * @var int
     */
    private $responseReasonCodePendingReview = 253;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->resultFactoryMock = $this->createMock(ResultInterfaceFactory::class);
        $this->resultMock = $this->createMock(ResultInterface::class);

        $this->validator = $objectManagerHelper->getObject(
            TransactionResponseValidator::class,
            [
                'resultInterfaceFactory' => $this->resultFactoryMock,
                'subjectReader' => new SubjectReader(),
            ]
        );
    }

    /**
     * @param array $transactionResponse
     * @param bool $isValid
     * @param array $errorMessages
     * @dataProvider scenarioProvider
     *
     * @return void
     */
    public function testValidateScenarios(array $transactionResponse, bool $isValid, array $errorMessages)
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->validator->validate([
            'response' => [
                'transactionResponse' => $transactionResponse,
            ]
        ]);

        $this->assertEquals($isValid, $args['isValid']);
        $this->assertEquals($errorMessages, $args['failsDescription']);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function scenarioProvider(): array
    {
        return [
            // This validator only cares about successful edge cases so test for default behavior
            [
                [
                    'responseCode' => '1',
                ],
                true,
                [],
            ],

            // Test for acceptable reason codes
            [
                [
                    'responseCode' => $this->responseCodeApproved,
                    'messages' => [
                        'message' => [
                            'code' => $this->responseReasonCodeApproved,
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'responseCode' => $this->responseCodeApproved,
                    'messages' => [
                        'message' => [
                            'code' => $this->responseReasonCodePendingReview,
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'responseCode' => $this->responseCodeApproved,
                    'messages' => [
                        'message' => [
                            'code' => $this->responseReasonCodePendingReviewAuthorized,
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'responseCode' => $this->responseCodeHeld,
                    'messages' => [
                        'message' => [
                            'code' => $this->responseReasonCodeApproved,
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'responseCode' => $this->responseCodeHeld,
                    'messages' => [
                        'message' => [
                            'code' => $this->responseReasonCodePendingReview,
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'responseCode' => $this->responseCodeHeld,
                    'messages' => [
                        'message' => [
                            'code' => $this->responseReasonCodePendingReviewAuthorized,
                        ],
                    ],
                ],
                true,
                [],
            ],

            // Test for reason codes that aren't acceptable
            [
                [
                    'responseCode' => $this->responseCodeApproved,
                    'messages' => [
                        'message' => [
                            [
                                'description' => 'bar',
                                'code' => 'foo',
                            ],
                        ],
                    ],
                ],
                false,
                ['foo'],
            ],
            [
                [
                    'responseCode' => $this->responseCodeApproved,
                    'messages' => [
                        'message' => [
                            // Alternate, non-array sytax
                            'text' => 'bar',
                            'code' => 'foo',
                        ],
                    ],
                ],
                false,
                ['foo'],
            ],
        ];
    }
}
