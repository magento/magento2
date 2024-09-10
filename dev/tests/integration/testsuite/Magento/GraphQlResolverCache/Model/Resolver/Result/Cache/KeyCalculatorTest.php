<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\Cache;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\CalculationException;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\ParentValueFactorProviderInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\GenericFactorProviderInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessorInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for graphql resolver-level cache key calculator.
 */
class KeyCalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->contextFactory = $this->objectManager->get(ContextFactoryInterface::class);
        parent::setUp();
    }

    /**
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testKeyCalculatorException()
    {
        $this->expectException(CalculationException::class);
        $this->expectExceptionMessage("Test message");
        $exceptionMessage = "Test message";

        $mock = $this->getMockBuilder(GenericFactorProviderInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactorName', 'getFactorValue'])
            ->getMock();
        $mock->expects($this->once())
            ->method('getFactorName')
            ->willThrowException(new \Exception($exceptionMessage));
        $mock->expects($this->never())
            ->method('getFactorValue')
            ->willReturn('value');

        $this->objectManager->addSharedInstance($mock, 'TestFactorProviderMock');

        /** @var Calculator $keyCalculator */
        $keyCalculator = $this->objectManager->create(
            Calculator::class,
            [
                'factorProviders' => [
                    'test' => 'TestFactorProviderMock'
                ]
            ]
        );
        $keyCalculator->calculateCacheKey();
    }

    /**
     * @param array $factorDataArray
     * @param array|null $parentResolverData
     * @param string|null $expectedCacheKey
     *
     * @return void
     *
     * @magentoAppArea graphql
     *
     * @dataProvider keyFactorDataProvider
     */
    public function testKeyCalculator(array $factorDataArray, ?array $parentResolverData, $expectedCacheKey)
    {
        $this->initMocksForObjectManager($factorDataArray, $parentResolverData);

        $keyFactorProvidersConfig = [];
        foreach ($factorDataArray as $factorData) {
            $keyFactorProvidersConfig[$factorData['name']] = $this->prepareFactorClassName($factorData);
        }
        /** @var Calculator $keyCalculator */
        $keyCalculator = $this->objectManager->create(
            Calculator::class,
            [
                'factorProviders' => $keyFactorProvidersConfig
            ]
        );
        $key = $keyCalculator->calculateCacheKey($parentResolverData);

        $this->assertEquals($expectedCacheKey, $key);

        $this->resetMocksForObjectManager($factorDataArray);
    }

    /**
     * Helper method to initialize object manager with mocks from given test data.
     *
     * @param array $factorDataArray
     * @param array|null $parentResolverData
     * @return void
     */
    private function initMocksForObjectManager(array $factorDataArray, ?array $parentResolverData)
    {
        foreach ($factorDataArray as $factor) {
            if ($factor['interface'] == GenericFactorProviderInterface::class) {
                $mock = $this->getMockBuilder($factor['interface'])
                    ->disableOriginalConstructor()
                    ->onlyMethods(['getFactorName', 'getFactorValue'])
                    ->getMock();
                $mock->expects($this->once())
                    ->method('getFactorName')
                    ->willReturn($factor['name']);
                $mock->expects($this->once())
                    ->method('getFactorValue')
                    ->willReturn($factor['value']);
            } else {
                $mock = $this->getMockBuilder($factor['interface'])
                    ->disableOriginalConstructor()
                    ->onlyMethods(['getFactorName', 'getFactorValue', 'getFactorValueForParentResolvedData'])
                    ->getMock();
                $mock->expects($this->once())
                    ->method('getFactorName')
                    ->willReturn($factor['name']);
                $mock->expects($this->never())
                    ->method('getFactorValue')
                    ->willReturn($factor['name']);
                $mock->expects($this->once())
                    ->method('getFactorValueForParentResolvedData')
                    ->with($this->contextFactory->get(), $parentResolverData)
                    ->willReturn($factor['value']);
            }
            $this->objectManager->addSharedInstance($mock, $this->prepareFactorClassName($factor));
        }
    }

    /**
     * Get class name from factor data.
     *
     * @param array $factor
     * @return string
     */
    private function prepareFactorClassName(array $factor)
    {
        return $factor['name'] . 'TestFactorMock';
    }

    /**
     * Reset all mocks for the object manager by given factor data.
     *
     * @param array $factorDataArray
     * @return void
     */
    private function resetMocksForObjectManager(array $factorDataArray)
    {
        foreach ($factorDataArray as $factor) {
            $this->objectManager->removeSharedInstance($this->prepareFactorClassName($factor));
        }
    }

    /**
     * Test data provider.
     *
     * @return array[]
     */
    public function keyFactorDataProvider()
    {
        $salt = Bootstrap::getObjectManager()->get(DeploymentConfig::class)
            ->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        return [
            'no factors' => [
                'factorProviders' => [],
                'parentResolverData' => null,
                'expectedCacheKey' => null
            ],
            'single factor' => [
                'factorProviders' => [
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'test',
                        'value' => 'testValue'
                    ],
                ],
                'parentResolverData' => null,
                'expectedCacheKey' => hash('sha256', strtoupper('testValue') . "|$salt"),
            ],
            'unsorted multiple factors' => [
                'factorProviders' => [
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'ctest',
                        'value' => 'c_testValue'
                    ],
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'atest',
                        'value' => 'a_testValue'
                    ],
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'btest',
                        'value' => 'b_testValue'
                    ],
                ],
                'parentResolverData' => null,
                'expectedCacheKey' => hash(
                    'sha256',
                    strtoupper('a_testValue|b_testValue|c_testValue') . "|$salt"
                ),
            ],
            'unsorted multiple factors with parent data' => [
                'factorProviders' => [
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'ctest',
                        'value' => 'c_testValue'
                    ],
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'atest',
                        'value' => 'a_testValue'
                    ],
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'btest',
                        'value' => 'object_123'
                    ],
                ],
                'parentResolverData' => [
                    'object_id' => 123
                ],
                'expectedCacheKey' => hash(
                    'sha256',
                    strtoupper('a_testValue|object_123|c_testValue') . "|$salt"
                ),
            ],
            'unsorted multifactor with no parent data and parent factored interface' => [
                'factorProviders' => [
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'ctest',
                        'value' => 'c_testValue'
                    ],
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'atest',
                        'value' => 'a_testValue'
                    ],
                    [
                        'interface' => GenericFactorProviderInterface::class,
                        'name' => 'btest',
                        'value' => 'some value'
                    ],
                ],
                'parentResolverData' => null,
                'expectedCacheKey' => hash(
                    'sha256',
                    strtoupper('a_testValue|some value|c_testValue') . "|$salt"
                ),
            ],
        ];
    }

    /**
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testValueProcessingIsCalledForParentValueFromCache()
    {
        $value = [
            'data' => 'some data',
            ValueProcessorInterface::VALUE_PROCESSING_REFERENCE_KEY => 'preprocess me'
        ];

        $this->initFactorMocks();

        $valueProcessorMock = $this->getMockBuilder(ValueProcessorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['preProcessParentValue'])
            ->getMockForAbstractClass();

        $valueProcessorMock->expects($this->once())
            ->method('preProcessParentValue')
            ->with($value);

        /** @var Calculator $keyCalculator */
        $keyCalculator = $this->objectManager->create(Calculator::class, [
            'valueProcessor' => $valueProcessorMock,
            'factorProviders' => [
                'context' => 'TestContextFactorMock',
                'parent_value' => 'TestValueFactorMock',
                'parent_processed_value' => 'TestProcessedValueFactorMock'
            ]
        ]);

        $key = $keyCalculator->calculateCacheKey($value);
        $salt = Bootstrap::getObjectManager()->get(DeploymentConfig::class)
            ->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        $expectedResult = hash('sha256', "|$salt");
        $this->assertEquals($expectedResult, $key);

        $this->objectManager->removeSharedInstance('TestValueFactorMock');
        $this->objectManager->removeSharedInstance('TestContextFactorMock');
    }

    /**
     * @return void
     */
    private function initFactorMocks()
    {
        $mockContextFactor = $this->getMockBuilder(GenericFactorProviderInterface::class)
            ->onlyMethods(['getFactorName', 'getFactorValue'])
            ->getMockForAbstractClass();

        $mockPlainParentValueFactor = $this->getMockBuilder(ParentValueFactorProviderInterface::class)
            ->onlyMethods(['getFactorName', 'getFactorValue', 'isRequiredOrigData'])
            ->getMockForAbstractClass();

        $mockPlainParentValueFactor->expects($this->any())->method('isRequiredOrigData')->willReturn(false);

        $mockProcessedParentValueFactor = $this->getMockBuilder(ParentValueFactorProviderInterface::class)
            ->onlyMethods(['getFactorName', 'getFactorValue', 'isRequiredOrigData'])
            ->getMockForAbstractClass();

        $mockProcessedParentValueFactor->expects($this->any())->method('isRequiredOrigData')->willReturn(true);

        $this->objectManager->addSharedInstance($mockPlainParentValueFactor, 'TestValueFactorMock');
        $this->objectManager->addSharedInstance($mockProcessedParentValueFactor, 'TestProcessedValueFactorMock');
        $this->objectManager->addSharedInstance($mockContextFactor, 'TestContextFactorMock');
    }

    /**
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testValueProcessingIsNotCalledForParentValueFromResolver()
    {
        $value = [
            'data' => 'some data'
        ];

        $this->initFactorMocks();

        $valueProcessorMock = $this->getMockBuilder(ValueProcessorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['preProcessParentValue'])
            ->getMockForAbstractClass();

        $valueProcessorMock->expects($this->never())
            ->method('preProcessParentValue');

        /** @var Calculator $keyCalculator */
        $keyCalculator = $this->objectManager->create(Calculator::class, [
            'valueProcessor' => $valueProcessorMock,
            'factorProviders' => [
                'context' => 'TestContextFactorMock',
                'parent_value' => 'TestValueFactorMock',
                'parent_processed_value' => 'TestProcessedValueFactorMock'
            ]
        ]);

        $key = $keyCalculator->calculateCacheKey($value);
        $salt = Bootstrap::getObjectManager()->get(DeploymentConfig::class)
            ->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        $expectedResult = hash('sha256', "|$salt");
        $this->assertEquals($expectedResult, $key);

        $this->objectManager->removeSharedInstance('TestValueFactorMock');
        $this->objectManager->removeSharedInstance('TestContextFactorMock');
    }

    /**
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testValueProcessingIsSkippedForContextOnlyFactors()
    {
        $mockContextFactor = $this->getMockBuilder(GenericFactorProviderInterface::class)
            ->onlyMethods(['getFactorName', 'getFactorValue'])
            ->getMockForAbstractClass();

        $value = ['data' => 'some data'];

        $this->objectManager->addSharedInstance($mockContextFactor, 'TestContextFactorMock');

        $valueProcessorMock = $this->getMockBuilder(ValueProcessorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['preProcessParentValue'])
            ->getMockForAbstractClass();

        $valueProcessorMock->expects($this->never())
            ->method('preProcessParentValue');

        /** @var Calculator $keyCalculator */
        $keyCalculator = $this->objectManager->create(Calculator::class, [
            'valueProcessor' => $valueProcessorMock,
            'factorProviders' => [
                'context' => 'TestContextFactorMock',
            ]
        ]);

        $key = $keyCalculator->calculateCacheKey($value);
        $salt = Bootstrap::getObjectManager()->get(DeploymentConfig::class)
            ->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        $expectedResult = hash('sha256', "|$salt");
        $this->assertEquals($expectedResult, $key);

        $this->objectManager->removeSharedInstance('TestContextFactorMock');
    }
}
