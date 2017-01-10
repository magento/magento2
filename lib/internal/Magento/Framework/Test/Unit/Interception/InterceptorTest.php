<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\Interception;

use Magento\Framework\Interception;

class InterceptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Sample\Interceptor
     */
    private $sampleInterceptor;

    /**
     * @var array
     */
    private $samplePlugins;

    /**
     * @var Interception\PluginListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pluginListMock;

    protected function setUp()
    {
        $this->pluginListMock = $this->getMockBuilder(Interception\PluginListInterface::class)
            ->getMockForAbstractClass();

        $this->sampleInterceptor = new Sample\Interceptor();
        $this->samplePlugins = [
            'plugin1' => new Sample\Plugin1(),
            'plugin2' => new Sample\Plugin2(),
            'plugin3' => new Sample\Plugin3(),
            'plugin4' => new Sample\Plugin4()
        ];

        $this->sampleInterceptor->setPluginList($this->pluginListMock);
    }

    public function testCallPlugins()
    {
        $subjectType = Sample\Entity::class;
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
                    Interception\DefinitionInterface::LISTENER_BEFORE => ['plugin1', 'plugin2'],
                    Interception\DefinitionInterface::LISTENER_AROUND => 'plugin3',
                    Interception\DefinitionInterface::LISTENER_AFTER => ['plugin1', 'plugin2', 'plugin3']
                ]
            ],
            [
                $subjectType,
                $method,
                'plugin3',
                [
                    Interception\DefinitionInterface::LISTENER_BEFORE => ['plugin4'],
                    Interception\DefinitionInterface::LISTENER_AROUND => 'plugin4',
                    Interception\DefinitionInterface::LISTENER_AFTER => ['plugin4']
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
            Sample\Plugin1::class . '::before' . $capMethod,
            Sample\Plugin2::class . '::before' . $capMethod,
            Sample\Plugin3::class . '::around' . $capMethod,
            Sample\Plugin4::class . '::before' . $capMethod,
            Sample\Plugin4::class . '::around' . $capMethod,
            Sample\Entity::class . '::' . $method,
            Sample\Plugin4::class . '::after' . $capMethod,
            Sample\Plugin1::class . '::after' . $capMethod,
            Sample\Plugin2::class . '::after' . $capMethod,
            Sample\Plugin3::class . '::after' . $capMethod
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
