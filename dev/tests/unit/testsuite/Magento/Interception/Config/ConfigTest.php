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

namespace Magento\Interception\Config;

require_once __DIR__ . '/../Custom/Module/Model/Item.php';
require_once __DIR__ . '/../Custom/Module/Model/Item/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainer/Enhanced.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemContainerPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Simple.php';
require_once __DIR__ . '/../Custom/Module/Model/ItemPlugin/Advanced.php';

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Interception\Config\Config
     */
    protected $_model;

    protected function setUp()
    {
        $readerMap = include(__DIR__ . '/../_files/reader_mock_map.php');
        $readerMock = $this->getMock('\Magento\ObjectManager\Config\Reader\Dom', array(), array(), '', false);
        $readerMock->expects($this->any())
            ->method('read')
            ->will($this->returnValueMap($readerMap));

        $configScopeMock = $this->getMock('Magento\Config\ScopeListInterface');
        $configScopeMock->expects($this->any())
            ->method('getAllScopes')
            ->will($this->returnValue(array('global', 'backend', 'frontend')));
        $cacheMock = $this->getMock('Magento\Cache\FrontendInterface');
        // turn cache off
        $cacheMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue(false));

        $omConfigMock = $this->getMock('Magento\ObjectManager\Config');
        $omConfigMock->expects($this->any())
            ->method('getInstanceType')
            ->will($this->returnArgument(0));
        $this->_model = new \Magento\Interception\Config\Config(
            $readerMock,
            $configScopeMock,
            $cacheMock,
            new \Magento\ObjectManager\Relations\Runtime(),
            $omConfigMock,
            null,
            null,
            'interception'
        );
    }

    /**
     * @param boolean $expectedResult
     * @param string $type
     * @dataProvider hasPluginsDataProvider
     */
    public function testHasPlugins($expectedResult, $type)
    {
        $this->assertEquals($expectedResult, $this->_model->hasPlugins($type));
    }

    public function hasPluginsDataProvider()
    {
        return array(
            // item container has plugins only in the backend scope
            array(
                true,
                'Magento\Interception\Custom\Module\Model\ItemContainer',
            ),
            array(
                true,
                'Magento\Interception\Custom\Module\Model\Item',
            ),
            array(
                true,
                'Magento\Interception\Custom\Module\Model\Item\Enhanced',
            ),
            array(
                // the following model has only inherited plugins
                true,
                'Magento\Interception\Custom\Module\Model\ItemContainer\Enhanced',
            )
        );
    }
}
