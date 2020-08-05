<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Compiler\Config;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Setup\Module\Di\Code\Reader\Type;
use Magento\Setup\Module\Di\Compiler\ArgumentsResolver;
use Magento\Setup\Module\Di\Compiler\ArgumentsResolverFactory;
use Magento\Setup\Module\Di\Compiler\Config\Reader;
use Magento\Setup\Module\Di\Definition\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $diContainerConfig;

    /**
     * @var MockObject
     */
    protected $configLoader;

    /**
     * @var MockObject
     */
    protected $argumentsResolverFactory;

    /**
     * @var MockObject
     */
    protected $argumentsResolver;

    /**
     * @var MockObject
     */
    protected $classReaderDecorator;

    /**
     * @var MockObject
     */
    protected $typeReader;

    protected function setUp(): void
    {
        $this->diContainerConfig =
            $this->getMockForAbstractClass(ConfigInterface::class);
        $this->configLoader =
            $this->createMock(ConfigLoader::class);

        $this->argumentsResolverFactory =
            $this->createMock(ArgumentsResolverFactory::class);
        $this->argumentsResolver = $this->createMock(ArgumentsResolver::class);
        $this->argumentsResolverFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->argumentsResolver);
        $this->classReaderDecorator =
            $this->createMock(ClassReaderDecorator::class);
        $this->typeReader = $this->createMock(Type::class);

        $this->model = new Reader(
            $this->diContainerConfig,
            $this->configLoader,
            $this->argumentsResolverFactory,
            $this->classReaderDecorator,
            $this->typeReader
        );
    }

    public function testGenerateCachePerScopeGlobal()
    {
        $definitionCollection = $this->getDefinitionsCollection();
        $this->diContainerConfig->expects($this->any())
            ->method('getVirtualTypes')
            ->willReturn($this->getVirtualTypes());
        $this->diContainerConfig->expects($this->any())
            ->method('getPreferences')
            ->willReturn($this->getPreferences());

        $getResolvedConstructorArgumentsMap = $this->getResolvedVirtualConstructorArgumentsMap(
            $definitionCollection,
            $this->getVirtualTypes()
        );

        $this->diContainerConfig->expects($this->any())
            ->method('getInstanceType')
            ->willReturnMap($this->getInstanceTypeMap($this->getVirtualTypes()));

        $this->diContainerConfig->expects($this->any())
            ->method('getPreference')
            ->willReturnMap($this->getPreferencesMap());

        $isConcreteMap = [];
        foreach ($definitionCollection->getInstancesNamesList() as $instanceType) {
            $isConcreteMap[] = [$instanceType, strpos($instanceType, 'Interface') === false];

            $getResolvedConstructorArgumentsMap[] = [
                $instanceType,
                $definitionCollection->getInstanceArguments($instanceType),
                $this->getResolvedArguments(
                    $definitionCollection->getInstanceArguments($instanceType)
                )
            ];
        }

        $this->typeReader->expects($this->any())
            ->method('isConcrete')
            ->willReturnMap($isConcreteMap);
        $this->argumentsResolver->expects($this->any())
            ->method('getResolvedConstructorArguments')
            ->willReturnMap($getResolvedConstructorArgumentsMap);

        $this->assertEquals(
            $this->getExpectedGlobalConfig(),
            $this->model->generateCachePerScope($definitionCollection, Area::AREA_GLOBAL)
        );
    }

    /**
     * @return array
     */
    private function getExpectedGlobalConfig()
    {
        return [
            'arguments' => [
                'ConcreteType1' => ['resolved_argument1', 'resolved_argument2'],
                'ConcreteType2' => ['resolved_argument1', 'resolved_argument2'],
                'virtualType1' => ['resolved_argument1', 'resolved_argument2']
            ],
            'preferences' => $this->getPreferences(),
            'instanceTypes' => $this->getVirtualTypes(),
        ];
    }

    /**
     * @return Collection
     */
    private function getDefinitionsCollection()
    {
        $definitionCollection = new Collection();
        $definitionCollection->addDefinition('ConcreteType1', ['argument1', 'argument2']);
        $definitionCollection->addDefinition('ConcreteType2', ['argument1', 'argument2']);
        $definitionCollection->addDefinition('Interface1', [null]);

        return $definitionCollection;
    }

    /**
     * @return array
     */
    private function getVirtualTypes()
    {
        return ['virtualType1' => 'ConcreteType1'];
    }

    /**
     * @return array
     */
    private function getPreferences()
    {
        return [
            'Interface1' => 'ConcreteType1',
            'ThirdPartyInterface' => 'ConcreteType2'
        ];
    }

    /**
     * @return array
     */
    private function getPreferencesMap()
    {
        return [
            ['ConcreteType1', 'ConcreteType1'],
            ['ConcreteType2', 'ConcreteType2'],
            ['Interface1', 'ConcreteType1'],
            ['ThirdPartyInterface', 'ConcreteType2']
        ];
    }

    /**
     * @param array $arguments
     * @return array|null
     */
    private function getResolvedArguments($arguments)
    {
        return empty($arguments) ? null : array_map(
            function ($argument) {
                return 'resolved_' . $argument;
            },
            $arguments
        );
    }

    /**
     * @param array $virtualTypes
     * @return array
     */
    private function getInstanceTypeMap($virtualTypes)
    {
        $getInstanceTypeMap = [];
        foreach ($virtualTypes as $virtualType => $concreteType) {
            $getInstanceTypeMap[] = [$virtualType, $concreteType];
        }

        return $getInstanceTypeMap;
    }

    /**
     * @param Collection $definitionCollection
     * @param array $virtualTypes
     * @return array
     */
    private function getResolvedVirtualConstructorArgumentsMap(Collection $definitionCollection, array $virtualTypes)
    {
        $getResolvedConstructorArgumentsMap = [];
        foreach ($virtualTypes as $virtualType => $concreteType) {
            $getResolvedConstructorArgumentsMap[] = [
                $virtualType,
                $definitionCollection->getInstanceArguments($concreteType),
                $this->getResolvedArguments(
                    $definitionCollection->getInstanceArguments($concreteType)
                )
            ];
        }
        return $getResolvedConstructorArgumentsMap;
    }
}
