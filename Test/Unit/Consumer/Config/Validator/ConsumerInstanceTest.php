<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\Validator\ConsumerInstance as ConsumerInstanceValidator;

/**
 * @codingStandardsIgnoreFile
 */
class ConsumerInstanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConsumerInstanceValidator
     */
    private $validator;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
    public function validConfigDataProvider()
    {
        return [
            'valid' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => \Magento\Framework\MessageQueue\BatchConsumer::class,
                        'handlers' => [
                            ['type' => 'handlerClassOne', 'method' => 'handlerMethodOne'],
                            ['type' => 'handlerClassTwo', 'method' => 'handlerMethodTwo'],
                        ],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
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
        $this->setExpectedException('\LogicException', $expectedExceptionMessage);
        $this->validator->validate($configData);
    }

    /**
     * @return array
     */
    public function invalidConfigDataProvider()
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
                    ]
                ],
                "'Magento\\Framework\\MessageQueue\\Test\\Unit\\Consumer\\Config\\Validator\\ConsumerInstanceTest' cannot be specified as 'consumerInstance' for 'consumer1' consumer, unless it implements 'Magento\\Framework\\MessageQueue\\ConsumerInterface' interface"
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
                    ]
                ],
                "'consumerClass1' does not exist and thus cannot be used as 'consumerInstance' for 'consumer1' consumer."
            ]
        ];
    }
}
