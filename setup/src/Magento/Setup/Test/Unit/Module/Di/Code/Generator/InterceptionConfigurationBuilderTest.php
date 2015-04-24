<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Generator;

use Magento\Framework\App\Interception\Cache\CompiledConfig;

class InterceptionConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Generator\InterceptionConfigurationBuilder
     */
    protected $model;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $interceptionConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pluginList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    protected function setUp()
    {
        $this->interceptionConfig = $this->getMock(
            'Magento\Framework\Interception\Config\Config',
            ['hasPlugins'],
            [],
            '',
            false
        );
        $this->pluginList = $this->getMock(
            'Magento\Setup\Module\Di\Code\Generator\PluginList',
            ['setInterceptedClasses', 'setScopePriorityScheme', 'getPluginsConfig'],
            [],
            '',
            false
        );
        $this->cacheManager = $this->getMock(
            'Magento\Framework\App\Cache\Manager',
            [],
            [],
            '',
            false
        );

        $this->typeReader = $this->getMock('Magento\Setup\Module\Di\Code\Reader\Type', ['isConcrete'], [], '', false);
        $this->model = new \Magento\Setup\Module\Di\Code\Generator\InterceptionConfigurationBuilder(
            $this->interceptionConfig,
            $this->pluginList,
            $this->typeReader,
            $this->cacheManager
        );
    }

    /**
     * @dataProvider getInterceptionConfigurationDataProvider
     */
    public function testGetInterceptionConfiguration($plugins)
    {
        $definedClasses = ['Class1'];
        $this->interceptionConfig->expects($this->once())
            ->method('hasPlugins')
            ->with('Class1')
            ->willReturn(true);
        $this->typeReader->expects($this->any())
            ->method('isConcrete')
            ->willReturnMap([
                ['Class1', true],
                ['instance', true],
            ]);
        $this->cacheManager->expects($this->once())
            ->method('setEnabled')
            ->with([CompiledConfig::TYPE_IDENTIFIER], true);
        $this->pluginList->expects($this->once())
            ->method('setInterceptedClasses')
            ->with($definedClasses);
        $this->pluginList->expects($this->once())
            ->method('setScopePriorityScheme')
            ->with(['global', 'areaCode']);
        $this->pluginList->expects($this->once())
            ->method('getPluginsConfig')
            ->willReturn(['instance' => $plugins]);

        $this->model->addAreaCode('areaCode');
        $this->model->getInterceptionConfiguration($definedClasses);
    }

    /**
     * @return array
     */
    public function getInterceptionConfigurationDataProvider()
    {
        return [
            [null],
            [['plugin' => ['instance' => 'someinstance']]],
            [['plugin' => ['instance' => 'someinstance'], 'plugin2' => ['instance' => 'someinstance']]]
        ];
    }
}
