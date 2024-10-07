<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\PluginList;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\ConfigLoaderInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\Interception\PluginListGenerator;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainerPlugin\Simple as ItemContainerPlugin;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash\Plugin as StartingBackslashPlugin;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\ObjectManager\Definition\Runtime;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Custom/Module/Model/Item.php';
require_once __DIR__ . '/../Custom/Module/Model/Item/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainerPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Advanced.php';
require_once __DIR__ . '/../Custom/Module/Model/StartingBackslash.php';
require_once __DIR__ . '/../Custom/Module/Model/StartingBackslash/Plugin.php';

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PluginListTest extends TestCase
{
    /**
     * @var PluginList
     */
    private $object;

    /**
     * @var ScopeInterface|MockObject
     */
    private $configScopeMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var ConfigLoaderInterface|MockObject
     */
    private $configLoaderMock;

    protected function setUp(): void
    {
        $loadScoped = include __DIR__ . '/../_files/load_scoped_mock_map.php';
        $readerMock = $this->createMock(Dom::class);

        $this->configScopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->addMethods(['get'])
            ->getMockForAbstractClass();
        // turn cache off
        $this->cacheMock->method('get')->willReturn(false);

        $omConfigMock =  $this->getMockForAbstractClass(
            ConfigInterface::class
        );

        $omConfigMock->method('getOriginalInstanceType')->willReturnArgument(0);

        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
        $objectManagerMock->method('get')->willReturnArgument(0);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->configLoaderMock = $this->getMockBuilder(ConfigLoaderInterface::class)
            ->onlyMethods(['load'])
            ->getMockForAbstractClass();
        $pluginListGeneratorMock = $this->getMockBuilder(PluginListGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadScopedVirtualTypes', 'inheritPlugins'])
            ->getMock();
        $pluginListGeneratorMock->method('loadScopedVirtualTypes')
            ->willReturnMap($loadScoped);

        $definitions = $this->getMockBuilder(Runtime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $definitions->method('getClasses')->willReturn([]);

        // tested class is a mock to be able to set its protected properties values in closure
        $this->object = $this->getMockBuilder(PluginList::class)
            ->disableProxyingToOriginalMethods()
            ->onlyMethods(['_inheritPlugins'])
            ->setConstructorArgs(
                [
                    'reader' => $readerMock,
                    'configScope' => $this->configScopeMock,
                    'cache' => $this->cacheMock,
                    'relations' => new \Magento\Framework\ObjectManager\Relations\Runtime(),
                    'omConfig' => $omConfigMock,
                    'definitions' => new \Magento\Framework\Interception\Definition\Runtime(),
                    'objectManager' => $objectManagerMock,
                    'classDefinitions' => $definitions,
                    'scopePriorityScheme' => ['global'],
                    'cacheId' => 'interception',
                    'serializer' => $this->serializerMock,
                    'configLoader' => $this->configLoaderMock,
                    'pluginListGenerator' => $pluginListGeneratorMock
                ]
            )
            ->getMock();
    }

    public function testGetPlugin()
    {
        $inheritPlugins = function ($type) {
            $inheritedItem = [
                Item::class => [
                    'advanced_plugin' => [
                        'sortOrder' => 5,
                        'instance' => Advanced::class,
                    ],
                    'simple_plugin' => [
                        'sortOrder' => 10,
                        'instance' => Simple::class
                    ]
                ]
            ];
            $processedItem = [
                'Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item_getName___self' => [
                    2 => 'advanced_plugin',
                    4 => ['advanced_plugin']
                ],
                'Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item_getName_advanced_plugin' => [
                    4 => ['simple_plugin']
                ]
            ];
            $inheritedItemContainer = [
                ItemContainer::class => [
                    'simple_plugin' => [
                        'sortOrder' => 15,
                        'instance' => ItemContainerPlugin::class
                    ]
                ]
            ];
            $processedItemContainer = [
                'Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer_getName___self' => [
                    4 => ['simple_plugin']
                ]
            ];
            $inheritedStartingBackslash = [
                StartingBackslash::class => [
                    'simple_plugin' => [
                        'sortOrder' => 20,
                        'instance' => StartingBackslashPlugin::class
                    ]
                ]
            ];

            if ($type === 'Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item') {
                $this->_inherited = $inheritedItem; /** @phpstan-ignore-line */
                $this->_processed = $processedItem; /** @phpstan-ignore-line */
            }
            if ($type === 'Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer') {
                $this->_inherited = array_merge($inheritedItem, $inheritedItemContainer); /** @phpstan-ignore-line */
                $this->_processed = array_merge($processedItem, $processedItemContainer); /** @phpstan-ignore-line */
            }
            if ($type === 'Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash') {
                /** @phpstan-ignore-next-line */
                $this->_inherited = array_merge($inheritedItem, $inheritedItemContainer, $inheritedStartingBackslash);
                $this->_processed = array_merge($processedItem, $processedItemContainer); /** @phpstan-ignore-line */
            }
        };
        $inheritPlugins = $inheritPlugins->bindTo($this->object, PluginList::class);
        $this->object->method('_inheritPlugins')->willReturnCallback($inheritPlugins);

        $this->configScopeMock->method('getCurrentScope')->willReturn('backend');
        $this->object->getNext(Item::class, 'getName');
        $this->object->getNext(ItemContainer::class, 'getName');
        $this->object->getNext(StartingBackslash::class, 'getName');
        $this->assertEquals(
            Simple::class,
            $this->object->getPlugin(
                Item::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            Advanced::class,
            $this->object->getPlugin(
                Item::class,
                'advanced_plugin'
            )
        );
        $this->assertEquals(
            ItemContainerPlugin::class,
            $this->object->getPlugin(
                ItemContainer::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            StartingBackslashPlugin::class,
            $this->object->getPlugin(
                StartingBackslash::class,
                'simple_plugin'
            )
        );
    }

    /**
     * @param array $expectedResult
     * @param string $type
     * @param string $method
     * @param string $scopeCode
     * @param string $code
     * @param array $scopePriorityScheme
     * @dataProvider getPluginsDataProvider
     */
    public function testGetPlugins(
        ?array $expectedResult,
        string $type,
        string $method,
        string $scopeCode,
        string $code = '__self',
        array $scopePriorityScheme = ['global']
    ): void {
        $this->setScopePriorityScheme($scopePriorityScheme);
        $this->configScopeMock->method('getCurrentScope')->willReturn($scopeCode);

        $inheritPlugins = function ($type) {
            $inheritedItem = [
                Item::class => [
                    'simple_plugin' => [
                        'sortOrder' => 10,
                        'instance' => Simple::class
                    ]
                ]
            ];
            $processedItem = [
                'Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item_getName___self' => [
                    4 => [
                        'simple_plugin'
                    ]
                ],
            ];

            if ($type === 'Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item') {
                $this->_inherited = $inheritedItem; /** @phpstan-ignore-line */
                $this->_processed = $processedItem; /** @phpstan-ignore-line */
            }
        };
        $inheritPlugins = $inheritPlugins->bindTo($this->object, PluginList::class);
        $this->object->method('_inheritPlugins')->willReturnCallback($inheritPlugins);

        $this->assertEquals($expectedResult, $this->object->getNext($type, $method, $code));
    }

    /**
     * @return array
     */
    public static function getPluginsDataProvider()
    {
        return [
            [
                [4 => ['simple_plugin']], Item::class,
                'getName',
                'global',
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_loadScopedData
     */
    public function testLoadScopedDataCached()
    {
        $this->configScopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn('scope');

        $data = [['key'], ['key'], ['key']];
        $serializedData = 'serialized data';

        $this->serializerMock->expects($this->never())
            ->method('serialize');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($data);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('global|scope|interception')
            ->willReturn($serializedData);

        $inheritPlugins = function ($type) {
            $inherited = [
                0 => 'key',
                'Type' => null
            ];
            $processed = [
                0 => 'key'
            ];

            if ($type === 'Type') {
                $this->_inherited = $inherited; /** @phpstan-ignore-line */
                $this->_processed = $processed; /** @phpstan-ignore-line */
            }
        };
        $inheritPlugins = $inheritPlugins->bindTo($this->object, PluginList::class);
        $this->object->method('_inheritPlugins')->willReturnCallback($inheritPlugins);

        $this->assertNull($this->object->getNext('Type', 'method'));
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_loadScopedData
     */
    public function testLoadScopedDataGenerated()
    {
        $this->configScopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn('scope');

        $data = [['key'], ['key'], ['key']];

        $this->configLoaderMock->expects($this->once())
            ->method('load')
            ->with('global|scope|interception')
            ->willReturn($data);

        $inheritPlugins = function ($type) {
            $inherited = [
                0 => 'key',
                'Type' => null
            ];
            $processed = [
                0 => 'key'
            ];

            if ($type === 'Type') {
                $this->_inherited = $inherited; /** @phpstan-ignore-line */
                $this->_processed = $processed; /** @phpstan-ignore-line */
            }
        };
        $inheritPlugins = $inheritPlugins->bindTo($this->object, PluginList::class);
        $this->object->method('_inheritPlugins')->willReturnCallback($inheritPlugins);

        $this->assertNull($this->object->getNext('Type', 'method'));
    }

    /**
     * @param array $areaCodes
     * @throws \ReflectionException
     */
    private function setScopePriorityScheme(array $areaCodes): void
    {
        $reflection = new \ReflectionClass($this->object);
        $reflection_property = $reflection->getProperty('_scopePriorityScheme');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->object, $areaCodes);
    }
}
