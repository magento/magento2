<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\BatchConsumer;
use Magento\Framework\MessageQueue\Consumer\Config\Validator\ConsumerInstance as ConsumerInstanceValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ConsumerInstanceTest extends TestCase
{
    /**
     * @var ConsumerInstanceValidator
     */
    private $validator;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(ConsumerInstanceValidator::class);
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
                        'consumerInstance' => BatchConsumer::class,
                        'handlers' => [
                            ['type' => 'handlerClassOne', 'method' => 'handlerMethodOne'],
                            ['type' => 'handlerClassTwo', 'method' => 'handlerMethodTwo'],
                        ],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => false
                    ]
                ]
            ]
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
    public static function invalidConfigDataProvider()
    {
        return [
            'invalid, consumerInstance not implementing consumer interface' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => ConsumerInstanceTest::class,
                        'handlers' => [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                // @codingStandardsIgnoreStart
                "'Magento\\Framework\\MessageQueue\\Test\\Unit\\Consumer\\Config\\Validator\\ConsumerInstanceTest'"
                . " cannot be specified as 'consumerInstance' for 'consumer1' consumer, unless it implements"
                . " 'Magento\\Framework\\MessageQueue\\ConsumerInterface' interface"
                // @codingStandardsIgnoreEnd
            ],
            'invalid, consumerInstance class does not exist' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [
                            [['type' => 'handlerClassOne', 'method' => 'handlerMethodOne']]
                        ],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                        'maxIdleTime' => '500',
                        'sleep' => '10',
                        'onlySpawnWhenMessageAvailable' => true
                    ]
                ],
                "'consumerClass1' does not exist and thus cannot be used as 'consumerInstance'"
                . " for 'consumer1' consumer."
            ]
        ];
    }
}
