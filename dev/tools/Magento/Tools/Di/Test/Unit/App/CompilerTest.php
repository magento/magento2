<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Test\Unit\App;

use Magento\Framework\App\Console\Response;
use Magento\Tools\Di\App\Compiler;
use Magento\Tools\Di\App\Task;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Compiler
     */
    private $application;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Tools\Di\App\Task\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    private $taskManagerMock;

    /**
     * @var Response | \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods([])
            ->getMock();
        $this->taskManagerMock = $this->getMockBuilder('Magento\Tools\Di\App\Task\Manager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Console\Response')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->application = new Compiler(
            $this->taskManagerMock,
            $this->objectManagerMock,
            $this->responseMock
        );
    }

    public function testLaunchSuccess()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('configure')
            ->with($this->getPreferences());
        $index = 0;
        foreach ($this->getOptions() as $code => $arguments) {
            $this->taskManagerMock->expects($this->at($index))
                ->method('addOperation')
                ->with($code, $arguments);
            $index++;
        }
        $this->taskManagerMock->expects($this->at($index))->method('process');
        $this->responseMock->expects($this->once())
            ->method('setCode')
            ->with(Response::SUCCESS);

        $this->assertInstanceOf('\Magento\Framework\App\Console\Response', $this->application->launch());
    }

    public function testLaunchException()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('configure')
            ->with($this->getPreferences());
        $code = key($this->getOptions());
        $arguments = current($this->getOptions());
        $exception = new Task\OperationException(
            'Unrecognized operation',
            Task\OperationException::UNAVAILABLE_OPERATION
        );

        $this->taskManagerMock->expects($this->once())
            ->method('addOperation')
            ->with($code, $arguments)
            ->willThrowException($exception);

        $this->taskManagerMock->expects($this->never())->method('process');
        $this->responseMock->expects($this->once())
            ->method('setCode')
            ->with(Response::ERROR);

        $this->assertInstanceOf('\Magento\Framework\App\Console\Response', $this->application->launch());
    }

    /**
     * Returns configured preferences
     *
     * @return array
     */
    private function getPreferences()
    {
        return [
            'preferences' =>
                [
                    'Magento\Tools\Di\Compiler\Config\WriterInterface' =>
                        'Magento\Tools\Di\Compiler\Config\Writer\Filesystem',
                    'Magento\Tools\Di\Compiler\Log\Writer\WriterInterface' =>
                        'Magento\Tools\Di\Compiler\Log\Writer\Console'
                ],
            'Magento\Tools\Di\Compiler\Config\ModificationChain' => [
                'arguments' => [
                    'modificationsList' => [
                        'BackslashTrim' =>
                            ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\BackslashTrim'],
                        'PreferencesResolving' =>
                            ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\PreferencesResolving'],
                        'InterceptorSubstitution' =>
                            ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\InterceptorSubstitution'],
                        'InterceptionPreferencesResolving' =>
                            ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\PreferencesResolving'],
                        'ArgumentsSerialization' =>
                            ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\ArgumentsSerialization'],
                    ]
                ]
            ],
            'Magento\Tools\Di\Code\Generator\PluginList' => [
                'arguments' => [
                    'cache' => [
                        'instance' => 'Magento\Framework\App\Interception\Cache\CompiledConfig'
                    ]
                ]
            ],
            'Magento\Tools\Di\Code\Reader\ClassesScanner' => [
                'arguments' => [
                    'excludePatterns' => [
                        'application' => '#^' . BP . '/app/code/[\\w]+/[\\w]+/Test#',
                        'framework' => '#^' . BP . '/lib/internal/[\\w]+/[\\w]+/([\\w]+/)?Test#'
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns options
     *
     * @return array
     */
    private function getOptions()
    {
        return  [
            Task\OperationFactory::REPOSITORY_GENERATOR => [
                'path' => BP . '/' . 'app/code',
                'filePatterns' => ['di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/']
            ],
            Task\OperationFactory::APPLICATION_CODE_GENERATOR => [
                BP . '/' . 'app/code', BP . '/' . 'lib/internal/Magento/Framework', BP . '/' . 'var/generation'
            ],
            Task\OperationFactory::INTERCEPTION => [
                'intercepted_paths' => [
                    BP . '/' . 'app/code',
                    BP . '/' . 'lib/internal/Magento/Framework',
                    BP . '/' . 'var/generation'
                ],
                'path_to_store' => BP . '/var/generation',
            ],
            Task\OperationFactory::AREA_CONFIG_GENERATOR => [
                BP . '/' . 'app/code', BP . '/' . 'lib/internal/Magento/Framework', BP . '/' . 'var/generation'
            ],
            Task\OperationFactory::INTERCEPTION_CACHE => [
                BP . '/' . 'app/code', BP . '/' . 'lib/internal/Magento/Framework', BP . '/' . 'var/generation'
            ]
        ];
    }
}
