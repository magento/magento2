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
namespace Magento\Framework\Interception\Config;


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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $omConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $definitionMock;

    protected function setUp()
    {
        $this->readerMock = $this->getMock(
            '\Magento\Framework\ObjectManager\Config\Reader\Dom',
            array(),
            array(),
            '',
            false
        );
        $this->configScopeMock = $this->getMock('Magento\Framework\Config\ScopeListInterface');
        $this->cacheMock = $this->getMock('Magento\Framework\Cache\FrontendInterface');
        $this->omConfigMock = $this->getMock('Magento\Framework\Interception\ObjectManager\Config');
        $this->definitionMock = $this->getMock('Magento\Framework\ObjectManager\Definition');
    }

    /**
     * @param boolean $expectedResult
     * @param string $type
     * @dataProvider hasPluginsDataProvider
     */
    public function testHasPluginsWhenDataIsNotCached($expectedResult, $type)
    {
        $readerMap = include(__DIR__ . '/../_files/reader_mock_map.php');
        $this->readerMock->expects($this->any())
            ->method('read')
            ->will($this->returnValueMap($readerMap));
        $this->configScopeMock->expects($this->any())
            ->method('getAllScopes')
            ->will($this->returnValue(array('global', 'backend', 'frontend')));
        // turn cache off
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue(false));
        $this->omConfigMock->expects($this->any())
            ->method('getOriginalInstanceType')
            ->will($this->returnValueMap(
                array(
                    array(
                        'Magento\Framework\Interception\Custom\Module\Model\ItemContainer',
                        'Magento\Framework\Interception\Custom\Module\Model\ItemContainer',
                    ),
                    array(
                        'Magento\Framework\Interception\Custom\Module\Model\Item',
                        'Magento\Framework\Interception\Custom\Module\Model\Item',
                    ),
                    array(
                        'Magento\Framework\Interception\Custom\Module\Model\Item\Enhanced',
                        'Magento\Framework\Interception\Custom\Module\Model\Item\Enhanced',
                    ),
                    array(
                        'Magento\Framework\Interception\Custom\Module\Model\ItemContainer\Enhanced',
                        'Magento\Framework\Interception\Custom\Module\Model\ItemContainer\Enhanced',
                    ),
                    array(
                        'Magento\Framework\Interception\Custom\Module\Model\ItemProxy',
                        'Magento\Framework\Interception\Custom\Module\Model\ItemProxy',
                    ),
                    array(
                        'virtual_custom_item',
                        'Magento\Framework\Interception\Custom\Module\Model\Item',
                    ),
                )
            ));
        $this->definitionMock->expects($this->any())->method('getClasses')->will($this->returnValue(
            array(
                'Magento\Framework\Interception\Custom\Module\Model\ItemProxy',
            )
        ));
        $model = new \Magento\Framework\Interception\Config\Config(
            $this->readerMock,
            $this->configScopeMock,
            $this->cacheMock,
            new \Magento\Framework\ObjectManager\Relations\Runtime(),
            $this->omConfigMock,
            $this->definitionMock,
            'interception'
        );

        $this->assertEquals($expectedResult, $model->hasPlugins($type));
    }

    /**
     * @param boolean $expectedResult
     * @param string $type
     * @dataProvider hasPluginsDataProvider
     */
    public function testHasPluginsWhenDataIsCached($expectedResult, $type)
    {
        $cacheId = 'interception';
        $interceptionData = array(
            'Magento\Framework\Interception\Custom\Module\Model\ItemContainer' => true,
            'Magento\Framework\Interception\Custom\Module\Model\Item' => true,
            'Magento\Framework\Interception\Custom\Module\Model\Item\Enhanced' => true,
            'Magento\Framework\Interception\Custom\Module\Model\ItemContainer\Enhanced' => true,
            'Magento\Framework\Interception\Custom\Module\Model\ItemProxy' => false,
            'virtual_custom_item' => true,
        );
        $this->readerMock->expects($this->never())->method('read');
        $this->cacheMock->expects($this->never())->method('save');
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->with($cacheId)
            ->will($this->returnValue(serialize($interceptionData)));
        $model = new \Magento\Framework\Interception\Config\Config(
            $this->readerMock,
            $this->configScopeMock,
            $this->cacheMock,
            new \Magento\Framework\ObjectManager\Relations\Runtime(),
            $this->omConfigMock,
            $this->definitionMock,
            $cacheId
        );

        $this->assertEquals($expectedResult, $model->hasPlugins($type));
    }

    public function hasPluginsDataProvider()
    {
        return array(
            // item container has plugins only in the backend scope
            array(
                true,
                'Magento\Framework\Interception\Custom\Module\Model\ItemContainer',
            ),
            array(
                true,
                'Magento\Framework\Interception\Custom\Module\Model\Item',
            ),
            array(
                true,
                'Magento\Framework\Interception\Custom\Module\Model\Item\Enhanced',
            ),
            array(
                // the following model has only inherited plugins
                true,
                'Magento\Framework\Interception\Custom\Module\Model\ItemContainer\Enhanced',
            ),
            array(
                false,
                'Magento\Framework\Interception\Custom\Module\Model\ItemProxy',
            ),
            array(
                true,
                'virtual_custom_item',
            )
        );
    }
}
