<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Generator;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Interception\Cache\CompiledConfig;
use Magento\Framework\Interception\Config\Config;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\InterceptableValidator;
use Magento\Setup\Module\Di\Code\Generator\InterceptionConfigurationBuilder;
use Magento\Setup\Module\Di\Code\Generator\PluginList;
use Magento\Setup\Module\Di\Code\Reader\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InterceptionConfigurationBuilderTest extends TestCase
{
    /**
     * @var InterceptionConfigurationBuilder
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $interceptionConfig;

    /**
     * @var MockObject
     */
    protected $pluginList;

    /**
     * @var MockObject
     */
    protected $typeReader;

    /**
     * @var MockObject
     */
    private $cacheManager;

    /**
     * @var InterceptableValidator|MockObject
     */
    private $interceptableValidator;

    /**
     * @var MockObject
     */
    private $omConfig;

    protected function setUp(): void
    {
        $this->interceptionConfig = $this->createPartialMock(Config::class, ['hasPlugins']);
        $this->pluginList = $this->createPartialMock(
            PluginList::class,
            ['setInterceptedClasses', 'setScopePriorityScheme', 'getPluginsConfig']
        );
        $this->cacheManager = $this->createMock(Manager::class);
        $this->interceptableValidator = $this->createMock(InterceptableValidator::class);
        $this->omConfig = $this->createMock(ConfigInterface::class);

        $this->typeReader = $this->createPartialMock(Type::class, ['isConcrete']);
        $this->model = new InterceptionConfigurationBuilder(
            $this->interceptionConfig,
            $this->pluginList,
            $this->typeReader,
            $this->cacheManager,
            $this->interceptableValidator,
            $this->omConfig
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

        $this->omConfig->expects($this->any())
            ->method('getOriginalInstanceType')
            ->willReturnMap([
                ['stdClass', 'stdClass'],
                ['virtualTypeClass', 'stdClass'],
            ]);

        $this->model->addAreaCode('areaCode');
        $this->model->getInterceptionConfiguration($definedClasses);
    }

    /**
     * @return array
     */
    public static function getInterceptionConfigurationDataProvider()
    {
        return [
            [null],
            [['plugin' => ['instance' => 'stdClass']]],
            [[
                'plugin'  => ['instance' => 'stdClass'],
                'plugin1' => ['instance' => 'stdClass'],
                'plugin2' => ['instance' => 'virtualTypeClass']
            ]],
            [['plugin' => ['instance' => 'virtualTypeClass']]],
        ];
    }
}
