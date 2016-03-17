<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\Framework\MessageQueue\Test\Unit\Code\Generator;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\Reflection\MethodsMap as ServiceMethodsMap;
use Magento\Framework\MessageQueue\Code\Generator\Config\RemoteServiceReader\Communication as RemoteServiceReader;

class RemoteServiceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommunicationConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $communicationConfigMock;

    /**
     * @var ServiceMethodsMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceMethodsMapMock;

    /**
     * @var RemoteServiceReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $communicationReaderMock;

    /**
     * @var \Magento\Framework\MessageQueue\Code\Generator\RemoteServiceGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generator;

    protected function setUp()
    {
        $this->communicationConfigMock = $this->getMockBuilder('Magento\Framework\Communication\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceMethodsMapMock = $this->getMockBuilder('Magento\Framework\Reflection\MethodsMap')
            ->disableOriginalConstructor()
            ->getMock();

        $this->communicationReaderMock = $this
            ->getMockBuilder('Magento\Framework\MessageQueue\Code\Generator\Config\RemoteServiceReader\Communication')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->generator = $objectManager->getObject(
            'Magento\Framework\MessageQueue\Code\Generator\RemoteServiceGenerator',
            [
                'communicationConfig' => $this->communicationConfigMock,
                'serviceMethodsMap' => $this->serviceMethodsMapMock,
                'communicationRemoteServiceReader' => $this->communicationReaderMock,
                'sourceClassName' => '\Magento\Customer\Api\CustomerRepositoryInterface',
                'resultClassName' => '\Magento\Customer\Api\CustomerRepositoryInterfaceRemote',
                'classGenerator' => null
            ]
        );
        parent::setUp();
    }

    public function testGenerate()
    {
        $this->serviceMethodsMapMock->expects($this->any())
            ->method('getMethodsMap')
            ->with('\Magento\Customer\Api\CustomerRepositoryInterface')
            ->willReturn(
                [
                    'save' => [],
                    'get' => [],
                    'getList' => [],
                    'delete' => [],
                ]
            );
        $this->serviceMethodsMapMock->expects($this->any())->method('getMethodParams')->willReturnMap(
            [
                [
                    '\Magento\Customer\Api\CustomerRepositoryInterface',
                    'save',
                    [
                        [
                            'name' => 'customer',
                            'type' => 'Magento\Customer\Api\Data\CustomerInterface',
                            'isDefaultValueAvailable' => false,
                            'defaultValue' => null,
                        ],
                        [
                            'name' => 'passwordHash',
                            'type' => 'string',
                            'isDefaultValueAvailable' => true,
                            'defaultValue' => 'default_string_value',
                        ],
                    ]
                ],
                [
                    '\Magento\Customer\Api\CustomerRepositoryInterface',
                    'get',
                    [
                        [
                            'name' => 'email',
                            'type' => 'string',
                            'isDefaultValueAvailable' => false,
                            'defaultValue' => null,
                        ],
                        [
                            'name' => 'websiteId',
                            'type' => 'int',
                            'isDefaultValueAvailable' => true,
                            'defaultValue' => null,
                        ],
                    ]
                ],
                [
                    '\Magento\Customer\Api\CustomerRepositoryInterface',
                    'getList',
                    [
                        [
                            'name' => 'searchCriteria',
                            'type' => 'Magento\Framework\Api\SearchCriteriaInterface',
                            'isDefaultValueAvailable' => false,
                            'defaultValue' => null,
                        ],
                    ]
                ],
                [
                    '\Magento\Customer\Api\CustomerRepositoryInterface',
                    'delete',
                    [
                        [
                            'name' => 'customerId',
                            'type' => 'void',
                            'isDefaultValueAvailable' => false,
                            'defaultValue' => null,
                        ],
                    ]
                ],
            ]
        );
        $this->communicationReaderMock->expects($this->any())->method('generateTopicName')->willReturnMap(
            [
                ['\Magento\Customer\Api\CustomerRepositoryInterface', 'save', 'topic.save'],
                ['\Magento\Customer\Api\CustomerRepositoryInterface', 'get', 'topic.get'],
                ['\Magento\Customer\Api\CustomerRepositoryInterface', 'getList', 'topic.getList'],
                ['\Magento\Customer\Api\CustomerRepositoryInterface', 'delete', 'topic.delete'],
            ]
        );

        $this->communicationConfigMock->expects($this->any())
            ->method('getTopic')
            ->willReturnMap(
                [
                    ['topic.save', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => true]],
                    ['topic.get', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => true]],
                    ['topic.getList', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => true]],
                    ['topic.delete', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => false]],
                ]
            );
        $expectedResult = file_get_contents(__DIR__ . '/_files/RemoteService.txt');
        $this->validateGeneratedCode($expectedResult);
    }

    /**
     * Check if generated code matches provided expected result.
     *
     * @param string $expectedResult
     * @return void
     */
    protected function validateGeneratedCode($expectedResult)
    {
        $reflectionObject = new \ReflectionObject($this->generator);
        $reflectionMethod = $reflectionObject->getMethod('_generateCode');
        $reflectionMethod->setAccessible(true);
        $generatedCode = $reflectionMethod->invoke($this->generator);
        $expectedResult = preg_replace('/\s+/', ' ', $expectedResult);
        $generatedCode = preg_replace('/\s+/', ' ', $generatedCode);
        $this->assertEquals($expectedResult, $generatedCode);
    }
}
