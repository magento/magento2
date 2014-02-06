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
 * @category    Magento
 * @package     Magento_Theme
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme config model
 */
namespace Magento\Theme\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_themeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configCacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutCacheMock;

    /**
     * @var \Magento\App\Config\Storage\WriterInterface
     */
    protected $_storeConfigWriter;

    /**
     * @var \Magento\Theme\Model\Config
     */
    protected $_model;

    protected function setUp()
    {
        /** @var $this->_themeMock \Magento\Core\Model\Theme */
        $this->_themeMock = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $this->_storeManagerMock = $this->getMockForAbstractClass(
            'Magento\Core\Model\StoreManagerInterface', array(), '', true, true, true,
            array('getStores', 'isSingleStoreMode')
        );
        $this->_configData = $this->getMock(
            'Magento\Core\Model\Config\Value',
            array('getCollection', 'addFieldToFilter', '__wakeup'),
            array(),
            '',
            false
        );
        $this->_configCacheMock = $this->getMockForAbstractClass('Magento\Cache\FrontendInterface');
        $this->_layoutCacheMock = $this->getMockForAbstractClass('Magento\Cache\FrontendInterface');

        $this->_storeConfigWriter = $this->getMock(
            'Magento\App\Config\Storage\WriterInterface', array('save', 'delete')
        );

        $this->_model = new \Magento\Theme\Model\Config(
            $this->_configData,
            $this->_storeConfigWriter,
            $this->_storeManagerMock,
            $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false),
            $this->_configCacheMock,
            $this->_layoutCacheMock
        );
    }

    protected function tearDown()
    {
        $this->_themeMock        = null;
        $this->_configData       = null;
        $this->_themeFactoryMock = null;
        $this->_configCacheMock  = null;
        $this->_layoutCacheMock  = null;
        $this->_model            = null;
    }

    /**
     * @covers \Magento\Theme\Model\Config::assignToStore
     */
    public function testAssignToStoreInSingleStoreMode()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->will($this->returnValue(true));

        $themePath = 'magento_blank';
        /** Unassign themes from store */
        $configEntity = new \Magento\Object(array('value' => 6, 'scope_id' => 8));

        $this->_configData->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($this->_configData));

        $this->_configData->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('scope', \Magento\Core\Model\ScopeInterface::SCOPE_STORES)
            ->will($this->returnValue($this->_configData));

        $this->_configData->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('path', \Magento\View\DesignInterface::XML_PATH_THEME_ID)
            ->will($this->returnValue(array($configEntity)));

        $this->_themeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(6));
        $this->_themeMock->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));

        $this->_storeConfigWriter->expects($this->once())
            ->method('delete');

        $this->_storeConfigWriter->expects($this->once())
            ->method('save');

        $this->_model->assignToStore($this->_themeMock, array(2, 3, 5));
    }

    /**
     * @covers \Magento\Theme\Model\Config::assignToStore
     */
    public function testAssignToStoreNonSingleStoreMode()
    {
        $this->_storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->will($this->returnValue(false));

        $themePath = 'magento_blank';
        /** Unassign themes from store */
        $configEntity = new \Magento\Object(array('value' => 6, 'scope_id' => 8));

        $this->_configData->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($this->_configData));

        $this->_configData->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('scope', \Magento\Core\Model\ScopeInterface::SCOPE_STORES)
            ->will($this->returnValue($this->_configData));

        $this->_configData->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('path', \Magento\View\DesignInterface::XML_PATH_THEME_ID)
            ->will($this->returnValue(array($configEntity)));

        $this->_themeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(6));
        $this->_themeMock->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));

        $this->_storeConfigWriter->expects($this->once())
            ->method('delete');

        $this->_storeConfigWriter->expects($this->exactly(3))
            ->method('save');

        $this->_model->assignToStore($this->_themeMock, array(2, 3, 5));
    }
}
