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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Interception\PluginList;

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
     * @var \Magento\Interception\Config\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    protected function setUp()
    {
        $fixtureBasePath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/..');
        $fileResolverMock = $this->getMock('Magento\Config\FileResolverInterface');
        $fileResolverMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap(array(
                array('di.xml', 'global', array($fixtureBasePath . '/Custom/Module/etc/di.xml')),
                array('di.xml', 'backend', array($fixtureBasePath . '/Custom/Module/etc/backend/di.xml')),
                array('di.xml', 'frontend', array($fixtureBasePath . '/Custom/Module/etc/frontend/di.xml')),
            )));

        $validationStateMock = $this->getMock('Magento\Config\ValidationStateInterface');
        $validationStateMock->expects($this->any())
            ->method('isValidated')
            ->will($this->returnValue(true));

        $reader = new \Magento\ObjectManager\Config\Reader\Dom(
            $fileResolverMock,
            new \Magento\ObjectManager\Config\Mapper\Dom(),
            new \Magento\ObjectManager\Config\SchemaLocator(),
            $validationStateMock
        );
        $this->_configScopeMock = $this->getMock('Magento\Config\ScopeInterface');
        $this->_configScopeMock->expects($this->any())
            ->method('getAllScopes')
            ->will($this->returnValue(array('global', 'backend', 'frontend')));
        $cacheMock = $this->getMock('Magento\Config\CacheInterface');
        // turn cache off
        $cacheMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(false));

        $omConfigMock = $this->getMock('Magento\ObjectManager\Config');
        $omConfigMock->expects($this->any())
            ->method('getInstanceType')
            ->will($this->returnArgument(0));
        $this->_model = new \Magento\Interception\PluginList\PluginList(
            $reader,
            $this->_configScopeMock,
            $cacheMock,
            new \Magento\ObjectManager\Relations\Runtime(),
            $omConfigMock,
            new \Magento\Interception\Definition\Runtime(),
            array('global'),
            null,
            'interception'
        );
    }

    /**
     * @param array $expectedResult
     * @param string $type
     * @param string $method
     * @param string $scenario
     * @param string $scopeCode
     * @dataProvider getPluginsDataProvider
     */
    public function testGetPlugins(array $expectedResult, $type, $method, $scenario, $scopeCode)
    {
        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->will($this->returnValue($scopeCode));
        $this->assertEquals(
            $expectedResult,
            $this->_model->getPlugins($type, $method, $scenario)
        );
    }

    /**
     * @return array
     */
    public function getPluginsDataProvider()
    {
        return array(
            array(
                array('Magento\Interception\Custom\Module\Model\ItemPlugin\Simple'),
                'Magento\Interception\Custom\Module\Model\Item',
                'getName',
                'after',
                'global',
            ),
            array(
                // advanced plugin has lower sort order
                array('Magento\Interception\Custom\Module\Model\ItemPlugin\Advanced',
                      'Magento\Interception\Custom\Module\Model\ItemPlugin\Simple'),
                'Magento\Interception\Custom\Module\Model\Item',
                'getName',
                'after',
                'backend',
            ),
            array(
                array('Magento\Interception\Custom\Module\Model\ItemPlugin\Advanced'),
                'Magento\Interception\Custom\Module\Model\Item',
                'getName',
                'around',
                'backend',
            ),
            array(
                // simple plugin is disabled in configuration for
                // \Magento\Interception\Custom\Module\Model\Item in frontend
                array(),
                'Magento\Interception\Custom\Module\Model\Item',
                'getName',
                'after',
                'frontend',
            ),
            // test plugin inheritance
            array(
                array('Magento\Interception\Custom\Module\Model\ItemPlugin\Simple'),
                'Magento\Interception\Custom\Module\Model\Item\Enhanced',
                'getName',
                'after',
                'global',
            ),
            array(
                // simple plugin is disabled in configuration for parent
                array('Magento\Interception\Custom\Module\Model\ItemPlugin\Advanced'),
                'Magento\Interception\Custom\Module\Model\Item\Enhanced',
                'getName',
                'after',
                'frontend',
            ),
            array(
                array('Magento\Interception\Custom\Module\Model\ItemPlugin\Advanced'),
                'Magento\Interception\Custom\Module\Model\Item\Enhanced',
                'getName',
                'around',
                'frontend',
            ),
            array(
                array(),
                'Magento\Interception\Custom\Module\Model\ItemContainer',
                'getName',
                'after',
                'global',
            ),
            array(
                array('Magento\Interception\Custom\Module\Model\ItemContainerPlugin\Simple'),
                'Magento\Interception\Custom\Module\Model\ItemContainer',
                'getName',
                'after',
                'backend',
            ),
        );
    }
}
