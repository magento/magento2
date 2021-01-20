<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Generator;

use Magento\Framework\App\Interception\Cache\CompiledConfig;

class InterceptionConfigurationBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Generator\InterceptionConfigurationBuilder
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $interceptionConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $pluginList;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $typeReader;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheManager;

    /**
     * @var \Magento\Framework\ObjectManager\InterceptableValidator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $interceptableValidator;

    protected function setUp(): void
    {
        $this->interceptionConfig =
            $this->createPartialMock(\Magento\Framework\Interception\Config\Config::class, ['hasPlugins']);
        $this->pluginList = $this->createPartialMock(
            \Magento\Setup\Module\Di\Code\Generator\PluginList::class,
            ['setInterceptedClasses', 'setScopePriorityScheme', 'getPluginsConfig']
        );
        $this->cacheManager = $this->createMock(\Magento\Framework\App\Cache\Manager::class);
        $this->interceptableValidator =
            $this->createMock(\Magento\Framework\ObjectManager\InterceptableValidator::class);

        $this->typeReader = $this->createPartialMock(\Magento\Setup\Module\Di\Code\Reader\Type::class, ['isConcrete']);
        $this->model = new \Magento\Setup\Module\Di\Code\Generator\InterceptionConfigurationBuilder(
            $this->interceptionConfig,
            $this->pluginList,
            $this->typeReader,
            $this->cacheManager,
            $this->interceptableValidator
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
        $this->interceptableValidator->expects($this->any())
            ->method('validate')
            ->with('Class1')
            ->willReturn(true);

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
