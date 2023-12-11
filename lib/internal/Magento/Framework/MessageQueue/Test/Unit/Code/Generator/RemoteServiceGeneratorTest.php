<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Code\Generator;

use Composer\Autoload\ClassLoader;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Communication\Config\ReflectionGenerator;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\MessageQueue\Code\Generator\RemoteServiceGenerator;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoteServiceGeneratorTest extends TestCase
{
    /**
     * @var CommunicationConfigInterface|MockObject
     */
    private $communicationConfig;

    /**
     * @var RemoteServiceGenerator|MockObject
     */
    private $generator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->communicationConfig = $this->getMockBuilder(CommunicationConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $loader = new ClassLoader();
        $loader->addPsr4(
            'Magento\\Framework\\MessageQueue\\Code\\Generator\\',
            __DIR__ . '/_files'
        );
        $loader->register();
    }

    /**
     * Checks a test case when generator should be possible to generate code
     * for a specified interface.
     *
     * @param string $sourceClassName
     * @param string $resultClassName
     * @param string $topicName
     * @param string $fileName
     * @dataProvider interfaceDataProvider
     */
    public function testGenerate($sourceClassName, $resultClassName, $topicName, $fileName)
    {
        $this->createGenerator($sourceClassName, $resultClassName);

        $this->communicationConfig->method('getTopic')
            ->willReturnMap(
                [
                    [$topicName . '.save', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => true]],
                    [$topicName . '.get', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => true]],
                    [$topicName . '.getById', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => true]],
                    [$topicName . '.getList', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => true]],
                    [$topicName . '.delete', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => true]],
                    [$topicName . '.deleteById', [CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => true]],
                ]
            );
        $expectedResult = file_get_contents(__DIR__ . '/_files/' . $fileName);
        $this->validateGeneratedCode($expectedResult);
    }

    /**
     * Get list of variations for testing remote service generator.
     *
     * @return array
     */
    public function interfaceDataProvider()
    {
        return [
            [
                '\\' . CustomerRepositoryInterface::class,
                '\\' . \Magento\Customer\Api\CustomerRepositoryInterfaceRemote::class,
                'magento.customer.api.customerRepositoryInterface',
                'RemoteService.txt'
            ],
            [
                '\\' . \Magento\Framework\MessageQueue\Code\Generator\TRepositoryInterface::class,
                '\\' . \Magento\Framework\MessageQueue\Code\Generator\TRepositoryInterfaceRemote::class,
                'magento.framework.messageQueue.code.generator.tRepositoryInterface',
                'TRemoteService.txt'
            ]
        ];
    }

    /**
     * Checks if generated code matches provided expected result.
     *
     * @param string $expectedResult
     * @return void
     */
    private function validateGeneratedCode($expectedResult)
    {
        $reflectionObject = new \ReflectionObject($this->generator);
        $reflectionMethod = $reflectionObject->getMethod('_generateCode');
        $reflectionMethod->setAccessible(true);
        $generatedCode = $reflectionMethod->invoke($this->generator);
        self::assertEquals($expectedResult, $generatedCode);
    }

    /**
     * Creates instance of RemoveServiceGenerator::class with all required dependencies.
     *
     * @param string $sourceClassName
     * @param string $resultClassName
     */
    private function createGenerator($sourceClassName, $resultClassName)
    {
        $methodMap = $this->createMethodMap();
        $this->generator = $this->objectManager->getObject(
            RemoteServiceGenerator::class,
            [
                'communicationConfig' => $this->communicationConfig,
                'serviceMethodsMap' => $methodMap,
                'sourceClassName' => $sourceClassName,
                'resultClassName' => $resultClassName,
                'classGenerator' => null
            ]
        );

        $reflectionGenerator = $this->objectManager->getObject(ReflectionGenerator::class);
        $this->objectManager->setBackwardCompatibleProperty(
            $this->generator,
            'reflectionGenerator',
            $reflectionGenerator
        );
    }

    /**
     * Creates instance of MethodsMap::class.
     *
     * @return MethodsMap
     */
    private function createMethodMap()
    {
        $cache = $this->getMockBuilder(FrontendInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cache->method('load')
            ->willReturn(false);

        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $typeProcessor = $this->objectManager->getObject(TypeProcessor::class);

        /** @var MethodsMap $serviceMethodMap */
        $serviceMethodMap = $this->objectManager->getObject(MethodsMap::class, [
            'cache' => $cache,
            'typeProcessor' => $typeProcessor
        ]);
        $this->objectManager->setBackwardCompatibleProperty(
            $serviceMethodMap,
            'serializer',
            $serializer
        );

        return $serviceMethodMap;
    }
}
