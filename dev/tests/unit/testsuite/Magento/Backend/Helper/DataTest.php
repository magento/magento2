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
 * @package     Magento_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \Magento\Core\Model\Config\Primary
     */
    protected $_primaryConfigMock;

    protected function setUp()
    {
        $this->_configMock = $this->getMock('Magento\Core\Model\Config', array(), array(), '', false, false);
        $this->_primaryConfigMock =
            $this->getMock('Magento\Core\Model\Config\Primary', array(), array(), '', false, false);

        $this->_helper = new \Magento\Backend\Helper\Data(
            $this->getMock('Magento\Core\Helper\Context', array(), array(), '', false, false),
            $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false, false),
            $this->_configMock,
            $this->_primaryConfigMock,
            $this->getMock('Magento\Core\Model\RouterList', array(), array(), '', false),
            $this->getMock('Magento\Core\Model\App', array(), array(), '', false),
            $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false),
            $this->getMock('Magento\Backend\Model\Auth', array(), array(), '', false),
            'backend',
            'custom_backend'
        );
    }

    public function testGetAreaFrontNameReturnsDefaultValueWhenCustomNotSet()
    {
        $this->_helper = new \Magento\Backend\Helper\Data(
            $this->getMock('Magento\Core\Helper\Context', array(), array(), '', false, false),
            $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false, false),
            $this->_configMock,
            $this->_primaryConfigMock,
            $this->getMock('Magento\Core\Model\RouterList', array(), array(), '', false),
            $this->getMock('Magento\Core\Model\App', array(), array(), '', false),
            $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false),
            $this->getMock('Magento\Backend\Model\Auth', array(), array(), '', false),
            'backend',
            ''
        );

        $this->_configMock->expects($this->once())->method('getValue')
            ->with(\Magento\Backend\Helper\Data::XML_PATH_USE_CUSTOM_ADMIN_PATH, 'default')
            ->will($this->returnValue(false));

        $this->assertEquals('backend', $this->_helper->getAreaFrontName());
    }

    public function testGetAreaFrontNameLocalConfigCustomFrontName()
    {
        $this->_configMock->expects($this->once())->method('getValue')
            ->with(\Magento\Backend\Helper\Data::XML_PATH_USE_CUSTOM_ADMIN_PATH, 'default')
            ->will($this->returnValue(false));

        $this->assertEquals('custom_backend', $this->_helper->getAreaFrontName());
    }

    public function testGetAreaFrontNameAdminConfigCustomFrontName()
    {
        $this->_configMock->expects($this->at(0))->method('getValue')
            ->with(\Magento\Backend\Helper\Data::XML_PATH_USE_CUSTOM_ADMIN_PATH, 'default')
            ->will($this->returnValue(true));

        $this->_configMock->expects($this->at(1))->method('getValue')
            ->with(\Magento\Backend\Helper\Data::XML_PATH_CUSTOM_ADMIN_PATH, 'default')
            ->will($this->returnValue('control'));

        $this->assertEquals('control', $this->_helper->getAreaFrontName());
    }

    public function testClearAreaFrontName()
    {
        $this->_configMock->expects($this->exactly(2))->method('getValue');

        $this->_helper->getAreaFrontName();
        $this->_helper->clearAreaFrontName();
        $this->_helper->getAreaFrontName();
    }

    public function testGetAreaFrontNameReturnsValueFromCache()
    {
        $this->_configMock->expects($this->once())->method('getValue');
        $this->_helper->getAreaFrontName();
        $this->_helper->getAreaFrontName();
    }
}
