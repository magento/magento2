<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Interception;

use Magento\Framework\Interception;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\Interception\PluginListInterface;
use Magento\Framework\Test\Unit\Interception\Sample\Entity;
use Magento\Framework\Test\Unit\Interception\Sample\Interceptor;
use Magento\Framework\Test\Unit\Interception\Sample\Plugin1;
use Magento\Framework\Test\Unit\Interception\Sample\Plugin2;
use Magento\Framework\Test\Unit\Interception\Sample\Plugin3;
use Magento\Framework\Test\Unit\Interception\Sample\Plugin4;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InterceptorTest extends TestCase
{
    /**
     * @var Interceptor
     */
    private $sampleInterceptor;

    /**
     * @var array
     */
    private $samplePlugins;

    /**
     * @var Interception\PluginListInterface|MockObject
     */
    private $pluginListMock;

    protected function setUp(): void
    {
        $this->pluginListMock = $this->getMockBuilder(PluginListInterface::class)
            ->getMockForAbstractClass();

        $this->sampleInterceptor = new Interceptor();
        $this->samplePlugins = [
            'plugin1' => new Plugin1(),
            'plugin2' => new Plugin2(),
            'plugin3' => new Plugin3(),
            'plugin4' => new Plugin4()
        ];

        $this->sampleInterceptor->setPluginList($this->pluginListMock);
    }

    public function testCallPlugins()
    {
        $subjectType = Entity::class;
        $method = 'doSomething';
        $capMethod = ucfirst($method);
        $pluginMap = [
            [$subjectType, 'plugin1', $this->samplePlugins['plugin1']],
            [$subjectType, 'plugin2', $this->samplePlugins['plugin2']],
            [$subjectType, 'plugin3', $this->samplePlugins['plugin3']],
            [$subjectType, 'plugin4', $this->samplePlugins['plugin4']]
        ];
        $pluginInfoMap = [
            [
                $subjectType,
                $method,
                null,
                [
                    DefinitionInterface::LISTENER_BEFORE => ['plugin1', 'plugin2'],
                    DefinitionInterface::LISTENER_AROUND => 'plugin3',
                    DefinitionInterface::LISTENER_AFTER => ['plugin1', 'plugin2', 'plugin3']
                ]
            ],
            [
                $subjectType,
                $method,
                'plugin3',
                [
                    DefinitionInterface::LISTENER_BEFORE => ['plugin4'],
                    DefinitionInterface::LISTENER_AROUND => 'plugin4',
                    DefinitionInterface::LISTENER_AFTER => ['plugin4']
                ]
            ],
            [
                $subjectType,
                $method,
                'plugin4',
                null
            ]
        ];
        $expectedPluginCalls = [
            Plugin1::class . '::before' . $capMethod,
            Plugin2::class . '::before' . $capMethod,
            Plugin3::class . '::around' . $capMethod,
            Plugin4::class . '::before' . $capMethod,
            Plugin4::class . '::around' . $capMethod,
            Entity::class . '::' . $method,
            Plugin4::class . '::after' . $capMethod,
            Plugin1::class . '::after' . $capMethod,
            Plugin2::class . '::after' . $capMethod,
            Plugin3::class . '::after' . $capMethod
        ];

        $this->pluginListMock->expects(static::any())
            ->method('getPlugin')
            ->willReturnMap($pluginMap);
        $this->pluginListMock->expects(static::exactly(3))
            ->method('getNext')
            ->willReturnMap($pluginInfoMap);

        $this->assertTrue($this->sampleInterceptor->$method());
        $this->assertEquals($expectedPluginCalls, $this->sampleInterceptor->getPluginCalls());
    }
}
