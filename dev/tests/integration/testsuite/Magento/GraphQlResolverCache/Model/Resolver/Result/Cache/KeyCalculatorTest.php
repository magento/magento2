<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\Cache;

use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\FactorProviderInterface;
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
    public function testKeyCalculatorErrorLogging()
    {
        $exceptionMessage = "Test message";
        $loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->onlyMethods(['warning'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $loggerMock->expects($this->once())
            ->method('warning')
            ->with("Unable to obtain cache key for resolver results. " . $exceptionMessage);

        $mock = $this->getMockBuilder(FactorProviderInterface::class)
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
                'logger' => $loggerMock,
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
            if ($factor['interface'] == FactorProviderInterface::class) {
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
        return [
            'no factors' => [
                'factorProviders' => [],
                'parentResolverData' => null,
                'expectedCacheKey' => null
            ],
            'single factor' => [
                'factorProviders' => [
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'test',
                        'value' => 'testValue'
                    ],
                ],
                'parentResolverData' => null,
                'expectedCacheKey' => hash('sha256', strtoupper('testValue')),
            ],
            'unsorted multiple factors' => [
                'factorProviders' => [
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'ctest',
                        'value' => 'c_testValue'
                    ],
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'atest',
                        'value' => 'a_testValue'
                    ],
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'btest',
                        'value' => 'b_testValue'
                    ],
                ],
                'parentResolverData' => null,
                'expectedCacheKey' => hash('sha256', strtoupper('a_testValue|b_testValue|c_testValue')),
            ],
            'unsorted multiple factors with parent data' => [
                'factorProviders' => [
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'ctest',
                        'value' => 'c_testValue'
                    ],
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'atest',
                        'value' => 'a_testValue'
                    ],
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'btest',
                        'value' => 'object_123'
                    ],
                ],
                'parentResolverData' => [
                    'object_id' => 123
                ],
                'expectedCacheKey' => hash('sha256', strtoupper('a_testValue|object_123|c_testValue')),
            ],
            'unsorted multifactor with no parent data and parent factored interface' => [
                'factorProviders' => [
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'ctest',
                        'value' => 'c_testValue'
                    ],
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'atest',
                        'value' => 'a_testValue'
                    ],
                    [
                        'interface' => FactorProviderInterface::class,
                        'name' => 'btest',
                        'value' => 'some value'
                    ],
                ],
                'parentResolverData' => null,
                'expectedCacheKey' => hash('sha256', strtoupper('a_testValue|some value|c_testValue')),
            ],
        ];
    }
}
