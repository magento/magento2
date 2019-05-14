<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Test\Unit\CompiledPluginList;

use Magento\Framework\CompiledInterception\Generator\CompiledPluginList;
use Magento\Framework\ObjectManagerInterface;

require_once __DIR__ . '/../Custom/Module/Model/Item.php';
require_once __DIR__ . '/../Custom/Module/Model/Item/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Advanced.php';

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompiledPluginListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompiledPluginList[]
     */
    private $objects;

    protected function setUp()
    {
        $this->objects = $this->createScopeReaders();
    }

    public function createScopeReaders()
    {
        $readerMap = include __DIR__ . '/../_files/reader_mock_map.php';
        $readerMock = $this->createMock(\Magento\Framework\ObjectManager\Config\Reader\Dom::class);
        $readerMock->expects($this->any())->method('read')->will($this->returnValueMap($readerMap));

        $omConfigMock =  $this->getMockForAbstractClass(
            \Magento\Framework\Interception\ObjectManager\ConfigInterface::class
        );

        $omConfigMock->expects($this->any())->method('getOriginalInstanceType')->will($this->returnArgument(0));
        $ret = [];
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        foreach($readerMap as $readerLine) {
            $ret[$readerLine[0]] = $objectManagerHelper->getObject(
                CompiledPluginList::class,
                [
                    'scope' => $readerLine[0],
                    'reader' => $readerMock,
                    'omConfig' => $omConfigMock,
                    'cachePath' => false
                ]
            );
        }
        return $ret;
    }

    public function testGetPlugin()
    {

        $this->objects['backend']->getNext(\Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class, 'getName');
        $this->assertEquals(
            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple::class,
            $this->objects['backend']->getPluginType(
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
            $this->objects['backend']->getPluginType(
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class,
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
                [4 => ['simple_plugin']], \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class,
                'getName',
                'global',
            ],
            [
                // advanced plugin has lower sort order
                [2 => 'advanced_plugin', 4 => ['advanced_plugin'], 1 => ['advanced_plugin']],
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class,
                'getName',
                'backend'
            ],
            [
                // advanced plugin has lower sort order
                [4 => ['simple_plugin']],
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class,
                'getName',
                'backend',
                'advanced_plugin'
            ],
            // simple plugin is disabled in configuration for
            // \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item in frontend
            [null, \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item::class, 'getName', 'frontend'],
            // test plugin inheritance
            [
                [4 => ['simple_plugin']],
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item\Enhanced::class,
                'getName',
                'global'
            ],
            [
                // simple plugin is disabled in configuration for parent
                [2 => 'advanced_plugin', 4 => ['advanced_plugin'], 1 => ['advanced_plugin']],
                \Magento\Framework\CompiledInterception\Test\Unit\Custom\Module\Model\Item\Enhanced::class,
                'getName',
                'frontend'
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_inheritPlugins
     */
    public function testInheritPluginsWithNonExistingClass()
    {
        $this->objects['frontend']->getNext('SomeType', 'someMethod');
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
