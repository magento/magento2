<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Interception\PluginList;


require_once __DIR__ . '/../Custom/Module/Model/Item.php';
require_once __DIR__ . '/../Custom/Module/Model/Item/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainerPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Advanced.php';
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
        $readerMock = $this->getMock('\Magento\Framework\ObjectManager\Config\Reader\Dom', array(), array(), '', false);
        $readerMock->expects($this->any())->method('read')->will($this->returnValueMap($readerMap));

        $this->_configScopeMock = $this->getMock('\Magento\Framework\Config\ScopeInterface');
        $this->_cacheMock = $this->getMock('Magento\Framework\Config\CacheInterface');
        // turn cache off
        $this->_cacheMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(false));

        $omConfigMock = $this->getMock('Magento\Framework\Interception\ObjectManager\Config');
        $omConfigMock->expects($this->any())->method('getOriginalInstanceType')->will($this->returnArgument(0));

        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnArgument(0));

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
            array('global'),
            'interception'
        );
    }

    public function testGetPlugin()
    {
        $this->_configScopeMock->expects($this->any())->method('getCurrentScope')->will($this->returnValue('backend'));
        $this->_model->getNext('Magento\Framework\Interception\Custom\Module\Model\Item', 'getName');
        $this->_model->getNext('Magento\Framework\Interception\Custom\Module\Model\ItemContainer', 'getName');

        $this->assertEquals(
            'Magento\Framework\Interception\Custom\Module\Model\ItemPlugin\Simple',
            $this->_model->getPlugin('Magento\Framework\Interception\Custom\Module\Model\Item', 'simple_plugin')
        );
        $this->assertEquals(
            'Magento\Framework\Interception\Custom\Module\Model\ItemPlugin\Advanced',
            $this->_model->getPlugin('Magento\Framework\Interception\Custom\Module\Model\Item', 'advanced_plugin')
        );
        $this->assertEquals(
            'Magento\Framework\Interception\Custom\Module\Model\ItemContainerPlugin\Simple',
            $this->_model->getPlugin(
                'Magento\Framework\Interception\Custom\Module\Model\ItemContainer',
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
        return array(
            array(
                array(4 => array('simple_plugin')),
                'Magento\Framework\Interception\Custom\Module\Model\Item',
                'getName',
                'global'
            ),
            array(
                // advanced plugin has lower sort order
                array(2 => 'advanced_plugin', 4 => array('advanced_plugin')),
                'Magento\Framework\Interception\Custom\Module\Model\Item',
                'getName',
                'backend'
            ),
            array(
                // advanced plugin has lower sort order
                array(4 => array('simple_plugin')),
                'Magento\Framework\Interception\Custom\Module\Model\Item',
                'getName',
                'backend',
                'advanced_plugin'
            ),
            // simple plugin is disabled in configuration for
            // \Magento\Framework\Interception\Custom\Module\Model\Item in frontend
            array(null, 'Magento\Framework\Interception\Custom\Module\Model\Item', 'getName', 'frontend'),
            // test plugin inheritance
            array(
                array(4 => array('simple_plugin')),
                'Magento\Framework\Interception\Custom\Module\Model\Item\Enhanced',
                'getName',
                'global'
            ),
            array(
                // simple plugin is disabled in configuration for parent
                array(2 => 'advanced_plugin', 4 => array('advanced_plugin')),
                'Magento\Framework\Interception\Custom\Module\Model\Item\Enhanced',
                'getName',
                'frontend'
            ),
            array(null, 'Magento\Framework\Interception\Custom\Module\Model\ItemContainer', 'getName', 'global'),
            array(
                array(4 => array('simple_plugin')),
                'Magento\Framework\Interception\Custom\Module\Model\ItemContainer',
                'getName',
                'backend'
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_inheritPlugins
     */
    public function testInheritPluginsWithNonExistingClass()
    {
        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->will($this->returnValue('frontend'));

        $this->_model->getNext('SomeType', 'someMethod');
    }

    /**
     * @covers \Magento\Framework\Interception\PluginList\PluginList::getNext
     * @covers \Magento\Framework\Interception\PluginList\PluginList::_loadScopedData
     */
    public function testLoadScopedDataCached()
    {
        $this->_configScopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->will($this->returnValue('scope'));

        $data = array(array('key'), array('key'), array('key'));

        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('global|scope|interception')
            ->will($this->returnValue(serialize($data)));

        $this->assertEquals(null, $this->_model->getNext('Type', 'method'));
    }
}
