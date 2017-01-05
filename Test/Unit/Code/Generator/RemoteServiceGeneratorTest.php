<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\Framework\MessageQueue\Test\Unit\Code\Generator;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\Reflection\MethodsMap as ServiceMethodsMap;
use Magento\Framework\Communication\Config\ReflectionGenerator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var ReflectionGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reflectionGeneratorMock;

    /**
     * @var \Magento\Framework\MessageQueue\Code\Generator\RemoteServiceGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generator;

    protected function setUp()
    {
        $this->communicationConfigMock = $this->getMockBuilder(\Magento\Framework\Communication\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceMethodsMapMock = $this->getMockBuilder(\Magento\Framework\Reflection\MethodsMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reflectionGeneratorMock = $this->getMockBuilder(ReflectionGenerator::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->generator = $objectManager->getObject(
            \Magento\Framework\MessageQueue\Code\Generator\RemoteServiceGenerator::class,
            [
                'communicationConfig' => $this->communicationConfigMock,
                'serviceMethodsMap' => $this->serviceMethodsMapMock,
                'sourceClassName' => '\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class,
                'resultClassName' => '\\' . \Magento\Customer\Api\CustomerRepositoryInterfaceRemote::class,
                'classGenerator' => null
            ]
        );
        $objectManager->setBackwardCompatibleProperty(
            $this->generator,
            'reflectionGenerator',
            $this->reflectionGeneratorMock
        );
        parent::setUp();
    }

    public function testGenerate()
    {
        $this->serviceMethodsMapMock->expects($this->any())
            ->method('getMethodsMap')
            ->with('\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class)
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
                    '\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class,
                    'save',
                    [
                        [
                            'name' => 'customer',
                            'type' => '\\' . \Magento\Customer\Api\Data\CustomerInterface::class,
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
                    '\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class,
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
                    '\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class,
                    'getList',
                    [
                        [
                            'name' => 'searchCriteria',
                            'type' => '\\' . \Magento\Framework\Api\SearchCriteriaInterface::class,
                            'isDefaultValueAvailable' => false,
                            'defaultValue' => null,
                        ],
                    ]
                ],
                [
                    '\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class,
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
        $this->reflectionGeneratorMock->expects($this->any())->method('generateTopicName')->willReturnMap(
            [
                ['\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class, 'save', 'topic.save'],
                ['\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class, 'get', 'topic.get'],
                ['\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class, 'getList', 'topic.getList'],
                ['\\' . \Magento\Customer\Api\CustomerRepositoryInterface::class, 'delete', 'topic.delete'],
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
