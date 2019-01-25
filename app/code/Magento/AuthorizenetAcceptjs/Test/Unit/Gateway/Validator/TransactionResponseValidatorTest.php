<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Gateway\Validator\TransactionResponseValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionResponseValidatorTest extends TestCase
{
    private const RESPONSE_CODE_APPROVED = 1;
    private const RESPONSE_CODE_HELD = 4;
    private const RESPONSE_REASON_CODE_APPROVED = 1;
    private const RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED = 252;
    private const RESPONSE_REASON_CODE_PENDING_REVIEW = 253;

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

    protected function setUp()
    {
        $this->resultFactoryMock = $this->createMock(ResultInterfaceFactory::class);
        $this->resultMock = $this->createMock(ResultInterface::class);

        $this->validator = new TransactionResponseValidator(
            $this->resultFactoryMock,
            new SubjectReader()
        );
    }

    /**
     * @param $transactionResponse
     * @param $isValid
     * @param $errorCodes
     * @param $errorMessages
     * @dataProvider scenarioProvider
     */
    public function testValidateScenarios($transactionResponse, $isValid, $errorCodes, $errorMessages)
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
                'transactionResponse' => $transactionResponse
            ]
        ]);

        $this->assertEquals($isValid, $args['isValid']);
        $this->assertEquals($errorCodes, $args['errorCodes']);
        $this->assertEquals($errorMessages, $args['failsDescription']);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function scenarioProvider()
    {
        return [
            // This validator only cares about successful edge cases so test for default behavior
            [
                [
                    'responseCode' => 'foo',
                ],
                true,
                [],
                []
            ],

            // Test for acceptable reason codes
            [
                [
                    'responseCode' => self::RESPONSE_CODE_APPROVED,
                    'messages' => [
                        'message' => [
                            'code' => self::RESPONSE_REASON_CODE_APPROVED,
                        ]
                    ]
                ],
                true,
                [],
                []
            ],
            [
                [
                    'responseCode' => self::RESPONSE_CODE_APPROVED,
                    'messages' => [
                        'message' => [
                            'code' => self::RESPONSE_REASON_CODE_PENDING_REVIEW,
                        ]
                    ]
                ],
                true,
                [],
                []
            ],
            [
                [
                    'responseCode' => self::RESPONSE_CODE_APPROVED,
                    'messages' => [
                        'message' => [
                            'code' => self::RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED,
                        ]
                    ]
                ],
                true,
                [],
                []
            ],
            [
                [
                    'responseCode' => self::RESPONSE_CODE_HELD,
                    'messages' => [
                        'message' => [
                            'code' => self::RESPONSE_REASON_CODE_APPROVED,
                        ]
                    ]
                ],
                true,
                [],
                []
            ],
            [
                [
                    'responseCode' => self::RESPONSE_CODE_HELD,
                    'messages' => [
                        'message' => [
                            'code' => self::RESPONSE_REASON_CODE_PENDING_REVIEW,
                        ]
                    ]
                ],
                true,
                [],
                []
            ],
            [
                [
                    'responseCode' => self::RESPONSE_CODE_HELD,
                    'messages' => [
                        'message' => [
                            'code' => self::RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED,
                        ]
                    ]
                ],
                true,
                [],
                []
            ],

            // Test for reason codes that aren't acceptable
            [
                [
                    'responseCode' => self::RESPONSE_CODE_APPROVED,
                    'messages' => [
                        'message' => [
                            [
                                'description' => 'bar',
                                'code' => 'foo',
                            ]
                        ]
                    ]
                ],
                false,
                ['foo'],
                ['bar']
            ],
            [
                [
                    'responseCode' => self::RESPONSE_CODE_APPROVED,
                    'messages' => [
                        'message' => [
                            // Alternate, non-array sytax
                            'text' => 'bar',
                            'code' => 'foo',
                        ]
                    ]
                ],
                false,
                ['foo'],
                ['bar']
            ],
        ];
    }
}
