<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\Validator\FieldsTypes as FieldsTypesValidator;

class FieldsTypesTest extends \PHPUnit\Framework\TestCase
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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
    public function validConfigDataProvider()
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
     */
    public function invalidConfigDataProvider()
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
                    ]
                ],
                "Type of 'maxMessages' field specified in configuration of 'consumer1' consumer is invalid."
                . " Given 'string', 'int|null' was expected."
            ],
        ];
    }
}
