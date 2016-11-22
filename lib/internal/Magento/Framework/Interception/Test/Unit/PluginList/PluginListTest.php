<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Test\Unit\PluginList;

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
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    protected function setUp()
    {
        $readerMap = include __DIR__ . '/../_files/reader_mock_map.php';
        $readerMock = $this->getMock(\Magento\Framework\ObjectManager\Config\Reader\Dom::class, [], [], '', false);
        $readerMock->expects($this->any())->method('read')->will($this->returnValueMap($readerMap));

        $this->_configScopeMock = $this->getMock(\Magento\Framework\Config\ScopeInterface::class);
        $this->_cacheMock = $this->getMock(\Magento\Framework\Config\CacheInterface::class);
        // turn cache off
        $this->_cacheMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(false));

        $omConfigMock =  $this->getMockForAbstractClass(
            \Magento\Framework\Interception\ObjectManager\ConfigInterface::class
        );

        $omConfigMock->expects($this->any())->method('getOriginalInstanceType')->will($this->returnArgument(0));

        $this->_objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $definitions = new \Magento\Framework\ObjectManager\Definition\Runtime();

        $this->_model = new \Magento\Framework\Interception\PluginList\PluginList(
            $readerMock,
            $this->_configScopeMock,
            $this->_cacheMock,
            new \Magento\Framework\ObjectManager\Relations\Runtime(),
            $omConfigMock,
            new \Magento\Framework\Interception\Definition\Runtime(),
            $this->_objectManagerMock,
            $definitions,
            ['global'],
            'interception'
        );
    }

    public function testGetPlugin()
    {
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnArgument(0));
        $this->_configScopeMock->expects($this->any())->method('getCurrentScope')->will($this->returnValue('backend'));
        $this->_model->getNext(\Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class, 'getName');
        $this->_model->getNext(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class,
            'getName'
        );
        $this->_model->getNext(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash::class,
            'getName'
        );

        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple::class,
            $this->_model->getPlugin(
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Advanced::class,
            $this->_model->getPlugin(
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                'advanced_plugin'
            )
        );
        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainerPlugin\Simple::class,
            $this->_model->getPlugin(
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer::class,
                'simple_plugin'
            )
        );
        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash\Plugin::class,
            $this->_model->getPlugin(
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
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnArgument(0));
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'getCurrentScope'
        )->will(
            $this->returnValue($scopeCode)
        );
        $this->assertEquals($expectedResult, $this->_model->getNext($type, $method, $code));
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
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnArgument(0));
        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->will($this->returnValue('frontend'));

        $this->_model->getNext('SomeType', 'someMethod');
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_inheritPlugins
     */
    public function testInheritPluginsWithNotExistingPlugin()
    {
        $loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->_objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Psr\Log\LoggerInterface::class)
            ->willReturn($loggerMock);
        $loggerMock->expects($this->once())
            ->method('info')
            ->with("Reference to undeclared plugin with name 'simple_plugin'.");
        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->will($this->returnValue('frontend'));

        $this->assertNull($this->_model->getNext('typeWithoutInstance', 'someMethod'));
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_loadScopedData
     */
    public function testLoadScopedDataCached()
    {
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnArgument(0));
        $this->_configScopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->will($this->returnValue('scope'));

        $data = [['key'], ['key'], ['key']];

        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('global|scope|interception')
            ->will($this->returnValue(serialize($data)));

        $this->assertEquals(null, $this->_model->getNext('Type', 'method'));
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_loadScopedData
     */
    public function testLoadScopeDataWithEmptyData()
    {
        $this->_objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnArgument(0));
        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->will($this->returnValue('emptyscope'));

        $this->assertEquals(
            [4 => ['simple_plugin']],
            $this->_model->getNext(
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                'getName'
            )
        );
        $this->assertEquals(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemPlugin\Simple::class,
            $this->_model->getPlugin(
                \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item::class,
                'simple_plugin'
            )
        );
    }
}
