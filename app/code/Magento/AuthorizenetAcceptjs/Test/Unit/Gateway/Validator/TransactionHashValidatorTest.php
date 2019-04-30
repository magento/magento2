<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Gateway\Validator\TransactionHashValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Validator\TransactionHashValidator
 */
class TransactionHashValidatorTest extends TestCase
{
    /**
     * @var ResultInterfaceFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var TransactionHashValidator
     */
    private $validator;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var ResultInterface|MockObject
     */
    private $resultMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->resultFactoryMock = $this->createMock(ResultInterfaceFactory::class);
        $this->configMock = $this->createMock(Config::class);
        $this->resultMock = $this->createMock(ResultInterface::class);

        $this->validator = $objectManagerHelper->getObject(
            TransactionHashValidator::class,
            [
                'resultInterfaceFactory' => $this->resultFactoryMock,
                'subjectReader' => new SubjectReader(),
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * @param array $response
     * @param bool $isValid
     * @param array $errorDescriptions
     * @dataProvider sha512ResponseProvider
     *
     * @return void
     */
    public function testValidateSha512HashScenarios(
        array $response,
        bool $isValid,
        array $errorDescriptions
    ) {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->configMock->method('getTransactionSignatureKey')
            ->willReturn('abc');
        $this->configMock->method('getLoginId')
            ->willReturn('username');

        $this->validator->validate($response);

        $this->assertSame($isValid, $args['isValid']);
        $this->assertEquals($errorDescriptions, $args['failsDescription']);
    }

    /**
     * @param array $response
     * @param bool $isValid
     * @param array $errorDescriptions
     * @dataProvider md5ResponseProvider
     *
     * @return void
     */
    public function testValidateMd5HashScenarios(
        array $response,
        bool $isValid,
        array $errorDescriptions
    ) {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->configMock->method('getLegacyTransactionHash')
            ->willReturn('abc');
        $this->configMock->method('getLoginId')
            ->willReturn('username');

        $this->validator->validate($response);

        $this->assertSame($isValid, $args['isValid']);
        $this->assertEquals($errorDescriptions, $args['failsDescription']);
    }

    /**
     * @return array
     */
    public function md5ResponseProvider(): array
    {
        return [
            [
                [
                    'response' => [
                        'transactionResponse' => [
                            'transId' => '123',
                            'transHash' => 'C8675D9F7BE7BE4A04C18EA1B6F7B6FD',
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'response' => [
                        'transactionResponse' => [
                            'transId' => '123',
                            'transHash' => 'C8675D9F7BE7BE4A04C18EA1B6F7B6FD',
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'amount' => '123.00',
                    'response' => [
                        'transactionResponse' => [
                            'transHash' => 'bad',
                        ],
                    ],
                ],
                false,
                ['ETHV'],
            ],
            [
                [
                    'amount' => '123.00',
                    'response' => [
                        'transactionResponse' => [
                            'refTransID' => '123',
                            'transId' => '123',
                            'transHash' => 'C8675D9F7BE7BE4A04C18EA1B6F7B6FD',
                        ],
                    ],
                ],
                true,
                [],
            ],
        ];
    }

    /**
     * @return array
     */
    public function sha512ResponseProvider(): array
    {
        return [
            [
                [
                    'response' => [
                        'transactionResponse' => [
                            'transId' => '123',
                            'refTransID' => '123',
                            'transHashSha2' => 'CC0FF465A081D98FFC6E502C40B2DCC7655ACF591F859135B6E66558D'
                                . '41E3A2C654D5A2ACF4749104F3133711175C232C32676F79F70211C2984B21A33D30DEE',
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'response' => [
                        'transactionResponse' => [
                            'transId' => '0',
                            'refTransID' => '123',
                            'transHashSha2' => '563D42F4A5189F74334088EF6A02E84F320CD8C005FB0DC436EF96084D'
                                . 'FAC0C76DE081DFC58A3BF825465C63B7F38E4D463025EAC44597A68C024CBBCE7A3159',
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'amount' => '123.00',
                    'response' => [
                        'transactionResponse' => [
                            'transId' => '0',
                            'transHashSha2' => 'DEE5309078D9F7A68BA4F706FB3E58618D3991A6A5E4C39DCF9C49E693'
                                . '673C38BD6BB15C235263C549A6B5F0B6D7019EC729E0C275C9FEA37FB91F8B612D0A5D',
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'amount' => '123.00',
                    'response' => [
                        'transactionResponse' => [
                            'transId' => '123',
                            'transHashSha2' => '1DBD16DED0DA02F52A22A9AD71A49F70BD2ECD42437552889912DD5CE'
                                . 'CBA0E09A5E8E6221DA74D98A46E5F77F7774B6D9C39CADF3E9A33D85870A6958DA7C8B2',
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'amount' => '123.00',
                    'response' => [
                        'transactionResponse' => [
                            'transId' => '123',
                            'refTransID' => '0',
                            'transHashSha2' => '1DBD16DED0DA02F52A22A9AD71A49F70BD2ECD42437552889912DD5CE'
                                . 'CBA0E09A5E8E6221DA74D98A46E5F77F7774B6D9C39CADF3E9A33D85870A6958DA7C8B2',
                        ],
                    ],
                ],
                true,
                [],
            ],
            [
                [
                    'amount' => '123.00',
                    'response' => [
                        'transactionResponse' => [
                            'transHashSha2' => 'bad',
                        ],
                    ],
                ],
                false,
                ['ETHV'],
            ],
        ];
    }
}
