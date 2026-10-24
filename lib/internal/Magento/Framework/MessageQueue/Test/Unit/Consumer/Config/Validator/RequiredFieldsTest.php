<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\Validator\RequiredFields as RequiredFieldsValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RequiredFieldsTest extends TestCase
{
    /**
     * @var RequiredFieldsValidator
     */
    private $validator;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(RequiredFieldsValidator::class);
    }

    /**     * @param array $configData
     */
    #[DataProvider('validConfigDataProvider')]
    public function testValidateValid($configData)
    {
        $this->validator->validate($configData);
    }

    /**
     * @return array
     */
    public static function validConfigDataProvider()
    {
        return [
            'valid' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ]
            ]
        ];
    }

    /**     * @param array $configData
     * @param string $expectedExceptionMessage
     */
    #[DataProvider('invalidConfigDataProvider')]
    public function testValidateInvalid($configData, $expectedExceptionMessage)
    {
        $this->expectException('\LogicException');
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->validator->validate($configData);
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function invalidConfigDataProvider()
    {
        return [
            'missing name' => [
                [
                    'consumer1' => [
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "'name' field must be specified for consumer 'consumer1'"
            ],
            'missing queue' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "'queue' field must be specified for consumer 'consumer1'"
            ],
            'missing consumerInstance' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "'consumerInstance' field must be specified for consumer 'consumer1'"
            ],
            'missing connection' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "'connection' field must be specified for consumer 'consumer1'"
            ],
            'missing handlers' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "'handlers' field must be specified for consumer 'consumer1'"
            ],
            'missing maxMessages' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "'maxMessages' field must be specified for consumer 'consumer1'"
            ],
            'missing maxIdleTime' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "'maxIdleTime' field must be specified for consumer 'consumer1'"
            ],
            'missing sleep' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "'sleep' field must be specified for consumer 'consumer1'"
            ],
            'missing onlySpawnWhenMessageAvailable' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                    ]
                ],
                "'onlySpawnWhenMessageAvailable' field must be specified for consumer 'consumer1'"
            ],
        ];
    }
}
