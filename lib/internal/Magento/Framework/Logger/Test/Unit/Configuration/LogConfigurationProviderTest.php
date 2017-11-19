<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Test\Unit\Configuration;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Logger\Configuration\LogConfigurationProvider;
use Magento\Framework\Logger\Configuration\Utility\ObjectInstantiator;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

class LogConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testInstantiationOfHandlerWithOnlyAClassName()
    {
        $deploymentConfig = $this->createDeployConfigMock(
            [
                'handlers' => [
                    'test' => 'TestHandler'
                ]
            ]
        );

        $testHandler = $this->createMock(HandlerInterface::class);

        $objectInstantiator = $this->createMock(ObjectInstantiator::class);
        $objectInstantiator
            ->method('createInstance')
            ->with($this->equalTo('TestHandler'))
            ->willReturn($testHandler);

        $logConfigProvider = new LogConfigurationProvider($deploymentConfig, $objectInstantiator);

        $this->assertEquals(
            spl_object_hash($testHandler),
            spl_object_hash($logConfigProvider->getHandlerByKey('test')),
            'Expected to get exact testHandler back'
        );
    }

    public function testInstantiationOfProcessorWithOnlyAClassName()
    {
        $deploymentConfig = $this->createDeployConfigMock(
            [
                'processors' => [
                    'test' => 'TestProcessor'
                ]
            ]
        );

        $testProcessor = (object) [];

        $objectInstantiator = $this->createMock(ObjectInstantiator::class);
        $objectInstantiator
            ->method('createInstance')
            ->with($this->equalTo('TestProcessor'))
            ->willReturn($testProcessor);

        $logConfigProvider = new LogConfigurationProvider($deploymentConfig, $objectInstantiator);

        $this->assertEquals(
            spl_object_hash($testProcessor),
            spl_object_hash($logConfigProvider->getProcessorByKey('test')),
            'Expected to get exact testProcessor back'
        );
    }

    public function testSimpleConstructorArgumentInConfiguration()
    {
        $deploymentConfig = $this->createDeployConfigMock(
            [
                'handlers' => [
                    'test' => [
                        'class' => 'TestHandler',
                        'level' => 300
                    ]
                ]
            ]
        );

        $testHandler = $this->createMock(HandlerInterface::class);

        $objectInstantiator = $this->createObjectInstantiatorMock([
            [$this->equalTo('TestHandler'), $this->equalTo(['level' => 300]), $testHandler]
        ]);

        $logConfigProvider = new LogConfigurationProvider($deploymentConfig, $objectInstantiator);

        $this->assertEquals(
            spl_object_hash($testHandler),
            spl_object_hash($logConfigProvider->getHandlerByKey('test')),
            'Expected to get exact testHandler back'
        );
    }

    public function testObjectInstanceConstructorArgumentInConfiguration()
    {
        $deploymentConfig = $this->createDeployConfigMock(
            [
                'handlers' => [
                    'test' => [
                        'class' => 'TestHandler',
                        '@random' => [
                            'class' => 'RandomObject'
                        ]
                    ]
                ]
            ]
        );

        $randomObject = (object)[];
        $testHandler = $this->createMock(HandlerInterface::class);

        $objectInstantiator = $this->createObjectInstantiatorMock([
            [$this->equalTo('RandomObject'), null, $randomObject],
            [$this->equalTo('TestHandler'), $this->equalTo(['random' => $randomObject]), $testHandler]
        ]);

        $logConfigProvider = new LogConfigurationProvider($deploymentConfig, $objectInstantiator);

        $this->assertEquals(
            spl_object_hash($testHandler),
            spl_object_hash($logConfigProvider->getHandlerByKey('test')),
            'Expected to get exact testHandler back'
        );
    }

    public function testHandlerReferenceArgumentForHandlerInConfiguration()
    {
        $deploymentConfig = $this->createDeployConfigMock(
            [
                'handlers' => [
                    'test' => [
                        'class' => 'TestHandler',
                        'handler' => 'test2'
                    ],
                    'test2' => [
                        'class' => 'RefHandler'
                    ]
                ]
            ]
        );

        $refHandler = $this->createMock(HandlerInterface::class);
        $testHandler = $this->createMock(HandlerInterface::class);

        $objectInstantiator = $this->createObjectInstantiatorMock([
            [$this->equalTo('RefHandler'), $this->equalTo([]), $refHandler],
            [$this->equalTo('TestHandler'), $this->equalTo(['handler' => $refHandler]), $testHandler]
        ]);

        $logConfigProvider = new LogConfigurationProvider($deploymentConfig, $objectInstantiator);

        $this->assertEquals(
            spl_object_hash($testHandler),
            spl_object_hash($logConfigProvider->getHandlerByKey('test')),
            'Expected to get exact testHandler back'
        );
    }

    public function testHandlersReferenceArgumentForHandlerInConfiguration()
    {
        $deploymentConfig = $this->createDeployConfigMock(
            [
                'handlers' => [
                    'test' => [
                        'class' => 'TestHandler',
                        'handlers' => ['test2', 'test3']
                    ],
                    'test2' => [
                        'class' => 'RefHandler1'
                    ],
                    'test3' => [
                        'class' => 'RefHandler2'
                    ]
                ]
            ]
        );

        $refHandler1 = $this->createMock(HandlerInterface::class);
        $refHandler2 = $this->createMock(HandlerInterface::class);
        $testHandler = $this->createMock(HandlerInterface::class);

        $objectInstantiator = $this->createObjectInstantiatorMock([
            [$this->equalTo('RefHandler1'), $this->equalTo([]), $refHandler1],
            [$this->equalTo('RefHandler2'), $this->equalTo([]), $refHandler2],
            [$this->equalTo('TestHandler'), $this->equalTo(['handlers' => [$refHandler1, $refHandler2]]), $testHandler]
        ]);

        $logConfigProvider = new LogConfigurationProvider($deploymentConfig, $objectInstantiator);

        $this->assertEquals(
            spl_object_hash($testHandler),
            spl_object_hash($logConfigProvider->getHandlerByKey('test')),
            'Expected to get exact testHandler back'
        );
    }

    public function testSetFormatterByFormatterReferenceForHandlerInConfiguration()
    {
        $deploymentConfig = $this->createDeployConfigMock(
            [
                'formatters' => [
                    'test' => [
                        'class' => 'TestFormatter'
                    ]
                ],
                'handlers' => [
                    'test' => [
                        'class' => 'TestHandler',
                        'formatter' => 'test'
                    ]
                ]
            ]
        );

        $testFormatter = $this->createMock(FormatterInterface::class);
        $testHandler = $this->createMock(HandlerInterface::class);
        $testHandler
            ->expects($this->once())
            ->method('setFormatter')
            ->with($this->equalTo($testFormatter));

        $objectInstantiator = $this->createObjectInstantiatorMock([
            [$this->equalTo('TestFormatter'), $this->equalTo([]), $testFormatter],
            [$this->equalTo('TestHandler'), $this->equalTo([]), $testHandler]
        ]);

        $logConfigProvider = new LogConfigurationProvider($deploymentConfig, $objectInstantiator);

        $this->assertEquals(
            spl_object_hash($testHandler),
            spl_object_hash($logConfigProvider->getHandlerByKey('test')),
            'Expected to get exact testHandler back'
        );
    }

    public function testPushProcessorByProcessorsReferenceForHandlerInConfiguration()
    {
        $deploymentConfig = $this->createDeployConfigMock(
            [
                'processors' => [
                    'test' => [
                        'class' => 'TestProcessor'
                    ],
                    'test2' => [
                        'class' => 'TestProcessor'
                    ]
                ],
                'handlers' => [
                    'test' => [
                        'class' => 'TestHandler',
                        'processors' => ['test', 'test2']
                    ]
                ]
            ]
        );

        $testProcessor = (object)[];
        $testHandler = $this->createMock(HandlerInterface::class);
        $testHandler
            ->expects($this->exactly(2))
            ->method('pushProcessor')
            ->with($this->equalTo($testProcessor));

        $objectInstantiator = $this->createObjectInstantiatorMock([
            [$this->equalTo('TestProcessor'), $this->equalTo([]), $testProcessor],
            [$this->equalTo('TestHandler'), $this->equalTo([]), $testHandler]
        ]);

        $logConfigProvider = new LogConfigurationProvider($deploymentConfig, $objectInstantiator);

        $this->assertEquals(
            spl_object_hash($testHandler),
            spl_object_hash($logConfigProvider->getHandlerByKey('test')),
            'Expected to get exact testHandler back'
        );
    }

    /**
     * Create DeploymentConfig Mock
     *
     * logConfiguration is the configuration given back when get('logging') is called
     *
     * @param array $logConfiguration
     * @return DeploymentConfig
     */
    private function createDeployConfigMock(array $logConfiguration)
    {
        $deploymentConfig = $this->createMock(DeploymentConfig::class);
        $deploymentConfig
            ->method('get')
            ->with($this->equalTo('logging'))
            ->willReturn($logConfiguration);

        return $deploymentConfig;
    }

    /**
     * Create ObjectInstantiator Mock
     *
     * The instanceConfigs follow the same pattern as returnValueMap but instead of comparing values
     * use PHPUnit constraints. A null value for a constraint ignores that argument value
     *
     * Examples:
     * [ $this->equalTo('Tester'), $this->equalTo([ 'level' => 300 ]), $testMock ]
     * [ $this->equalTo('Tester'), null, $testMock ]
     *
     * @param array $instanceConfigs
     * @return ObjectInstantiator
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function createObjectInstantiatorMock(array $instanceConfigs)
    {
        $objectInstantiator = $this->createMock(ObjectInstantiator::class);
        $objectInstantiator
            ->method('createInstance')
            ->will($this->returnCallback(function (string $class, array $arguments = []) use ($instanceConfigs) {
                foreach ($instanceConfigs as $instanceConfig) {
                    if ($instanceConfig[0] !== null && !$instanceConfig[0]->evaluate($class, '', true)) {
                        continue;
                    }
                    if ($instanceConfig[1] !== null && !$instanceConfig[1]->evaluate($arguments, '', true)) {
                        continue;
                    }
                    return $instanceConfig[2];
                }

                throw new \RuntimeException(
                    'Unexpected ObjectInstance: ' . var_export($class, true) . ' / ' . var_export($arguments, true)
                );
            }));

        return $objectInstantiator;
    }
}
