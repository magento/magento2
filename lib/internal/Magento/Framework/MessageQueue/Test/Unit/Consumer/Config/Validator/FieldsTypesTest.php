<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\Validator\FieldsTypes as FieldsTypesValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class FieldsTypesTest extends TestCase
{
    /**
     * @var FieldsTypesValidator
     */
    private $validator;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(FieldsTypesValidator::class);
    }

    /**
     * @dataProvider validConfigDataProvider
     * @param array $configData
     */
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
            ],
            'valid, maxMessages == null' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => null,
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ]
            ],
            'valid, maxIdleTime == null' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => null,
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ]
            ],
            'valid, sleep == null' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => null,
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ]
            ],
            'valid, onlySpawnWhenMessageAvailable == null' => [
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
                        'onlySpawnWhenMessageAvailable' => null
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider invalidConfigDataProvider
     * @param array $configData
     * @param string $expectedExceptionMessage
     */
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
            'invalid name' => [
                [
                    'consumer1' => [
                        'name' => true,
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
                "Type of 'name' field specified in configuration of 'consumer1' consumer is invalid."
                . " Given 'boolean', 'string' was expected."
            ],
            'invalid queue' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 1,
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "Type of 'queue' field specified in configuration of 'consumer1' consumer is invalid."
                . " Given 'integer', 'string' was expected."
            ],
            'invalid consumerInstance' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => (object)[],
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "Type of 'consumerInstance' field specified in configuration of 'consumer1' consumer is invalid."
                . " Given 'object', 'string' was expected."
            ],
            'invalid connection' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => [],
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "Type of 'connection' field specified in configuration of 'consumer1' consumer is invalid."
                . " Given 'array', 'string' was expected."
            ],
            'invalid handlers' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => '',
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "Type of 'handlers' field specified in configuration of 'consumer1' consumer is invalid."
                . " Given 'string', 'array' was expected."
            ],
            'invalid maxMessages' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => 'abc',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "Type of 'maxMessages' field specified in configuration of 'consumer1' consumer is invalid."
                . " Given 'string', 'int|null' was expected."
            ],
            'invalid maxIdleTime' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => 'abc',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "Type of 'maxIdleTime' field specified in configuration of 'consumer1' consumer is invalid."
                . " Given 'string', 'int|null' was expected."
            ],
            'invalid sleep' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => 'abc',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "Type of 'sleep' field specified in configuration of 'consumer1' consumer is invalid."
                . " Given 'string', 'int|null' was expected."
            ],
            'onlySpawnWhenMessageAvailable' => [
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
                        'onlySpawnWhenMessageAvailable' => 'yes'
                    ]
                ],
                "Type of 'onlySpawnWhenMessageAvailable' field specified in configuration of 'consumer1' consumer "
                . "is invalid. Given 'string', 'boolean|null' was expected."
            ]
        ];
    }
}
