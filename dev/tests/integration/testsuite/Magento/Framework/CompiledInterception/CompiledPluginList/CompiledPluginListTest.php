<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\CompiledInterception\CompiledPluginList;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\CompiledInterception\Generator\CompiledPluginList;
use Magento\Framework\CompiledInterception\Generator\FileCache;
use Magento\Framework\CompiledInterception\Generator\NoSerialize;
use Magento\Framework\CompiledInterception\Generator\StaticScope;
use Magento\Framework\CompiledInterception\CompiledPluginList\Custom\Module\Model\Item;
use Magento\Framework\CompiledInterception\CompiledPluginList\Custom\Module\Model\Item\Enhanced;
use Magento\Framework\CompiledInterception\CompiledPluginList\Custom\Module\Model\ItemPlugin\Advanced;
use Magento\Framework\CompiledInterception\CompiledPluginList\Custom\Module\Model\ItemPlugin\Simple;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompiledPluginListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompiledPluginList[]
     */
    private $objects;

    protected function setUp(): void
    {
        $this->objects = $this->createScopeReaders();
    }

    public function createScopeReaders()
    {
        $readerMap = include __DIR__ . '/_files/reader_mock_map.php';
        $readerMock = $this->createMock(Dom::class);
        $readerMock->method('read')->willReturnMap($readerMap);

        $omMock = $this->createMock(ObjectManager::class);
        $omMock->method('get')->with(LoggerInterface::class)->willReturn(new NullLogger());

        $omConfigMock =  $this->getMockForAbstractClass(
            ConfigInterface::class
        );

        $omConfigMock->method('getOriginalInstanceType')->willReturnArgument(0);
        $ret = [];
        $objectManagerHelper = new ObjectManagerHelper($this);
        $directoryList = ObjectManager::getInstance()->get(DirectoryList::class);
        //clear static cache
        $fileCache = new FileCache($directoryList);
        $fileCache->clean();
        foreach ($readerMap as $readerLine) {
            $pluginList = ObjectManager::getInstance()->create(
                PluginList::class,
                [
                    'objectManager' => $omMock,
                    'configScope' => new StaticScope($readerLine[0]),
                    'reader' => $readerMock,
                    'omConfig' => $omConfigMock,
                    'cache' => $fileCache,
                    'cachePath' => false,
                    'serializer' => new NoSerialize()
                ]
            );
            $ret[$readerLine[0]] = $objectManagerHelper->getObject(
                CompiledPluginList::class,
                [
                    'pluginList' => $pluginList,
                ]
            );
        }
        return $ret;
    }

    public function testGetPlugin()
    {
        $this->objects['backend']->getNext(Item::class, 'getName');
        $this->assertEquals(
            Simple::class,
            $this->objects['backend']->getPluginType(
                Item::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            Advanced::class,
            $this->objects['backend']->getPluginType(
                Item::class,
                'advanced_plugin'
            )
        );
    }

    /**
     * @param $expectedResult
     * @param $type
     * @param $method
     * @param $scopeCode
     * @param string $code
     * @dataProvider getPluginsDataProvider
     */
    public function testGetPlugins($expectedResult, $type, $method, $scopeCode, $code = '__self')
    {
        $this->assertEquals($expectedResult, $this->objects[$scopeCode]->getNext($type, $method, $code));
    }

    /**
     * @return array
     */
    public function getPluginsDataProvider()
    {
        return [
            [
                [4 => ['simple_plugin']], Item::class,
                'getName',
                'global',
            ],
            [
                // advanced plugin has lower sort order
                [2 => 'advanced_plugin', 4 => ['advanced_plugin'], 1 => ['advanced_plugin']],
                Item::class,
                'getName',
                'backend'
            ],
            [
                // advanced plugin has lower sort order
                [4 => ['simple_plugin']],
                Item::class,
                'getName',
                'backend',
                'advanced_plugin'
            ],
            // simple plugin is disabled in configuration for
            // \Magento\Framework\CompiledInterception\CompiledPluginList\Custom\Module\Model\Item
            // in frontend
            [null, Item::class, 'getName', 'frontend'],
            // test plugin inheritance
            [
                [4 => ['simple_plugin']],
                Enhanced::class,
                'getName',
                'global'
            ],
            [
                // simple plugin is disabled in configuration for parent
                [2 => 'advanced_plugin', 4 => ['advanced_plugin'], 1 => ['advanced_plugin']],
                Enhanced::class,
                'getName',
                'frontend'
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_inheritPlugins
     */
    public function testInheritPluginsWithNonExistingClass()
    {
        $this->objects['frontend']->getNext('SomeType', 'someMethod');
        $this->expectException(InvalidArgumentException::class);
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_inheritPlugins
     */
    public function testInheritPluginsWithNotExistingPlugin()
    {
        $this->assertNull($this->objects['frontend']->getNext('typeWithoutInstance', 'someMethod'));
    }
}
