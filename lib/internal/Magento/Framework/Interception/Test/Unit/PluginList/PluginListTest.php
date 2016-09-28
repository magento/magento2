<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Test\Unit\PluginList;

use Magento\Framework\Json\JsonInterface;

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
class PluginListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Interception\PluginList\PluginList
     */
    protected $object;

    /**
     * @var \Magento\Framework\Config\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configScopeMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    protected function setUp()
    {
        $readerMap = include __DIR__ . '/../_files/reader_mock_map.php';
        $readerMock = $this->getMock(\Magento\Framework\ObjectManager\Config\Reader\Dom::class, [], [], '', false);
        $readerMock->expects($this->any())->method('read')->will($this->returnValueMap($readerMap));

        $this->configScopeMock = $this->getMock(\Magento\Framework\Config\ScopeInterface::class);
        $this->cacheMock = $this->getMock(\Magento\Framework\Config\CacheInterface::class);
        // turn cache off
        $this->cacheMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(false));

        $omConfigMock =  $this->getMockForAbstractClass(
            \Magento\Framework\Interception\ObjectManager\ConfigInterface::class
        );

        $omConfigMock->expects($this->any())->method('getOriginalInstanceType')->will($this->returnArgument(0));

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())->method('get')->will($this->returnArgument(0));

        $definitions = new \Magento\Framework\ObjectManager\Definition\Runtime();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManagerHelper->getObject(
            \Magento\Framework\Interception\PluginList\PluginList::class,
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
                'cacheId' => 'interception'
            ]
        );
        $jsonMock = $this->getMock(JsonInterface::class, [], [], '', false);
        $jsonMock->method('encode')
            ->willReturnCallback(function ($string) {
                return json_encode($string);
            });
        $jsonMock->method('decode')
            ->willReturnCallback(function ($string) {
                return json_decode($string, true);
            });
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->object,
            'json',
            $jsonMock
        );
    }

    public function testGetPlugin()
    {
        $this->configScopeMock->expects($this->any())->method('getCurrentScope')->will($this->returnValue('backend'));
        $this->object->getNext(\Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class, 'getName');
        $this->object->getNext(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class,
            'getName'
        );
        $this->object->getNext(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash::class,
            'getName'
        );

        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple::class,
            $this->object->getPlugin(
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
            $this->object->getPlugin(
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                'advanced_plugin'
            )
        );
        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainerPlugin\Simple::class,
            $this->object->getPlugin(
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash\Plugin::class,
            $this->object->getPlugin(
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash::class,
                'simple_plugin'
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
        $this->configScopeMock->expects(
            $this->any()
        )->method(
            'getCurrentScope'
        )->will(
            $this->returnValue($scopeCode)
        );
        $this->assertEquals($expectedResult, $this->object->getNext($type, $method, $code));
    }

    /**
     * @return array
     */
    public function getPluginsDataProvider()
    {
        return [
            [
                [4 => ['simple_plugin']], \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                'getName',
                'global',
            ],
            [
                // advanced plugin has lower sort order
                [2 => 'advanced_plugin', 4 => ['advanced_plugin']],
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                'getName',
                'backend'
            ],
            [
                // advanced plugin has lower sort order
                [4 => ['simple_plugin']],
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                'getName',
                'backend',
                'advanced_plugin'
            ],
            // simple plugin is disabled in configuration for
            // \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item in frontend
            [null, \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class, 'getName', 'frontend'],
            // test plugin inheritance
            [
                [4 => ['simple_plugin']],
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced::class,
                'getName',
                'global'
            ],
            [
                // simple plugin is disabled in configuration for parent
                [2 => 'advanced_plugin', 4 => ['advanced_plugin']],
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item\Enhanced::class,
                'getName',
                'frontend'
            ],
            [
                null,
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class,
                'getName',
                'global'
            ],
            [
                [4 => ['simple_plugin']],
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class,
                'getName',
                'backend'
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
        $this->configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->will($this->returnValue('frontend'));

        $this->object->getNext('SomeType', 'someMethod');
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_loadScopedData
     */
    public function testLoadScopedDataCached()
    {
        $this->configScopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->will($this->returnValue('scope'));

        $data = [['key'], ['key'], ['key']];

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with('global|scope|interception')
            ->will($this->returnValue(json_encode($data)));

        $this->assertEquals(null, $this->object->getNext('Type', 'method'));
    }
}
