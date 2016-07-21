<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\ConfigInterface;

/**
 * @covers Magento\Framework\MessageQueue\MessageValidator
 */
class MessageValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var MessageValidator */
    protected $model;

    /** @var ConfigInterface */
    protected $configMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configMock = $this->getMockBuilder('Magento\Framework\MessageQueue\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            'Magento\Framework\MessageQueue\MessageValidator',
            [
                'queueConfig' => $this->configMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Specified topic "customer.created" is not declared.
     */
    public function testValidateInvalidTopic()
    {
        $this->model->validate('customer.created', 'Some message', true);
    }

    public function testValidateValidObjectType()
    {
        $this->configMock->expects($this->any())->method('getTopic')->willReturn($this->getQueueConfigDataObjectType());
        $object = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model->validate('customer.created', $object, true);
    }

    public function testValidateValidMethodType()
    {
        $this->configMock->expects($this->any())->method('getTopic')->willReturn($this->getQueueConfigDataMethodType());
        $object = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model->validate('customer.created', [$object, 'password', 'redirect'], true);
    }

    public function testEncodeValidMessageObjectType()
    {
        $this->configMock->expects($this->any())->method('getTopic')->willReturn($this->getQueueConfigDataObjectType());
        $this->model->validate('customer.created', [], true);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Data in topic "customer.created" must be of type "Magento\Customer\Api\Data\CustomerInt
     */
    public function testEncodeInvalidMessageMethodType()
    {
        $this->configMock->expects($this->any())->method('getTopic')->willReturn($this->getQueueConfigDataMethodType());
        $this->model->validate('customer.created', [1, 2, 3], true);
    }

    /**
     * Data provider for queue config of object type
     * @return array
     */
    private function getQueueConfigDataObjectType()
    {
        return [
            QueueConfig::TOPIC_SCHEMA => [
                QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                QueueConfig::TOPIC_SCHEMA_VALUE => 'Magento\Customer\Api\Data\CustomerInterface'
            ]
        ];
    }

    /**
     * Data provider for queue config of method type
     * @return array
     */
    private function getQueueConfigDataMethodType()
    {
        return [
            QueueConfig::TOPIC_SCHEMA => [
                QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_METHOD,
                QueueConfig::TOPIC_SCHEMA_VALUE => [
                    [
                        'param_name' => 'customer',
                        'param_position' => 0,
                        'is_required' => true,
                        'param_type' => 'Magento\Customer\Api\Data\CustomerInterface',
                    ],
                    [
                        'param_name' => 'password',
                        'param_position' => 1,
                        'is_required' => false,
                        'param_type' => 'string',
                    ],
                    [
                        'param_name' => 'redirectUrl',
                        'param_position' => 2,
                        'is_required' => false,
                        'param_type' => 'string',
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider getQueueConfigRequestType
     */
    public function testInvalidMessageType($requestType, $message, $expectedResult = null)
    {
        $this->configMock->expects($this->any())->method('getTopic')->willReturn($requestType);
        if ($expectedResult) {
            $this->setExpectedException('InvalidArgumentException', $expectedResult);
        }
        $this->model->validate('topic', $message);
    }

    public function getQueueConfigRequestType()
    {
        $customerMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $customerMockTwo = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        return [
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'string'
                ]],
                'valid string',
                null
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'string'
                ]],
                1,
                'Data in topic "topic" must be of type "string". "int" given.'
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'string[]'
                ]],
                ['string1', 'string2'],
                null
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'string[]'
                ]],
                [],
                null
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'string[]'
                ]],
                'single string',
                'Data in topic "topic" must be of type "string[]". "string" given.'
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'Magento\Customer\Api\Data\CustomerInterface'
                ]],
                $customerMock,
                null
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'Magento\Customer\Api\Data\CustomerInterface'
                ]],
                'customer',
                'Data in topic "topic" must be of type "Magento\Customer\Api\Data\CustomerInterface". "string" given.'
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'Magento\Customer\Api\Data\CustomerInterface[]'
                ]],
                [$customerMock, $customerMockTwo],
                null
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'Magento\Customer\Api\Data\CustomerInterface[]'
                ]],
                [],
                null
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'Magento\Customer\Api\Data\CustomerInterface[]'
                ]],
                'customer',
                'Data in topic "topic" must be of type "Magento\Customer\Api\Data\CustomerInterface[]". "string" given.'
            ],
            [
                [QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT,
                    QueueConfig::TOPIC_SCHEMA_VALUE => 'Magento\Customer\Api\Data\CustomerInterface[]'
                ]],
                $customerMock,
                'Data in topic "topic" must be of type "Magento\Customer\Api\Data\CustomerInterface[]". '
            ],
        ];
    }
}
