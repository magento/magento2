<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\MessageValidator;

/**
 * @covers Magento\Framework\MessageQueue\MessageValidator
 * @SuppressWarnings(PHPMD)
 */
class MessageValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var MessageValidator */
    protected $model;

    /** @var CommunicationConfig|\PHPUnit\Framework\MockObject\MockObject */
    protected $communicationConfigMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManager->getObject(MessageValidator::class);
        $this->communicationConfigMock = $this->getMockBuilder(CommunicationConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'communicationConfig',
            $this->communicationConfigMock
        );
    }

    /**
     */
    public function testValidateInvalidTopic()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Specified topic "customer.created" is not declared.');

        $this->model->validate('customer.created', 'Some message', true);
    }

    public function testValidateValidObjectType()
    {
        $this->communicationConfigMock->expects($this->any())->method('getTopic')->willReturn(
            $this->getQueueConfigDataObjectType()
        );
        $object = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model->validate('customer.created', $object, true);
    }

    public function testValidateValidMethodType()
    {
        $this->communicationConfigMock->expects($this->any())->method('getTopic')->willReturn(
            $this->getQueueConfigDataMethodType()
        );
        $object = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model->validate('customer.created', [$object, 'password', 'redirect'], true);
    }

    public function testEncodeValidMessageObjectType()
    {
        $this->communicationConfigMock->expects($this->any())->method('getTopic')->willReturn(
            $this->getQueueConfigDataObjectType()
        );
        $this->model->validate('customer.created', [], true);
    }

    /**
     */
    public function testEncodeInvalidMessageMethodType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data in topic "customer.created" must be of type "Magento\\Customer\\Api\\Data\\CustomerInt');

        $this->communicationConfigMock->expects($this->any())->method('getTopic')->willReturn(
            $this->getQueueConfigDataMethodType()
        );
        $this->model->validate('customer.created', [1, 2, 3], true);
    }

    /**
     * Data provider for queue config of object type
     *
     * @return array
     */
    private function getQueueConfigDataObjectType()
    {
        return [
            CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
            CommunicationConfig::TOPIC_REQUEST => \Magento\Customer\Api\Data\CustomerInterface::class
        ];
    }

    /**
     * Data provider for queue config of method type
     *
     * @return array
     */
    private function getQueueConfigDataMethodType()
    {
        return [
            CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_METHOD,
            CommunicationConfig::TOPIC_REQUEST => [
                [
                    'param_name' => 'customer',
                    'param_position' => 0,
                    'is_required' => true,
                    'param_type' => \Magento\Customer\Api\Data\CustomerInterface::class,
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
        ];
    }

    /**
     * @dataProvider getQueueConfigRequestType
     */
    public function testInvalidMessageType($requestType, $message, $expectedResult = null)
    {
        $this->communicationConfigMock->expects($this->any())->method('getTopic')->willReturn($requestType);
        if ($expectedResult) {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionMessage($expectedResult);
        }
        $this->model->validate('topic', $message);
    }

    /**
     * @return array
     */
    public function getQueueConfigRequestType()
    {
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $customerMockTwo = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        return [
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => 'string'
                ],
                'valid string',
                null
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => 'string'
                ],
                1,
                'Data in topic "topic" must be of type "string". "int" given.'
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => 'string[]'
                ],
                ['string1', 'string2'],
                null
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => 'string[]'
                ],
                [],
                null
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => 'string[]'
                ],
                'single string',
                'Data in topic "topic" must be of type "string[]". "string" given.'
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => \Magento\Customer\Api\Data\CustomerInterface::class
                ],
                $customerMock,
                null
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => \Magento\Customer\Api\Data\CustomerInterface::class
                ],
                'customer',
                'Data in topic "topic" must be of type "Magento\Customer\Api\Data\CustomerInterface". "string" given.'
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => 'Magento\Customer\Api\Data\CustomerInterface[]'
                ],
                [$customerMock, $customerMockTwo],
                null
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => 'Magento\Customer\Api\Data\CustomerInterface[]'
                ],
                [],
                null
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => 'Magento\Customer\Api\Data\CustomerInterface[]'
                ],
                'customer',
                'Data in topic "topic" must be of type "Magento\Customer\Api\Data\CustomerInterface[]". "string" given.'
            ],
            [
                [
                    CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
                    CommunicationConfig::TOPIC_REQUEST => 'Magento\Customer\Api\Data\CustomerInterface[]'
                ],
                $customerMock,
                'Data in topic "topic" must be of type "Magento\Customer\Api\Data\CustomerInterface[]". '
            ],
        ];
    }
}
