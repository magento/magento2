<?php
/**
 * Test class for \Magento\Core\Model\Config\Loader
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Config;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Loader
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_primaryConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_modulesReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseConfigMock;

    protected function setUp()
    {
        $this->_primaryConfigMock = $this->getMock(
            'Magento\Core\Model\Config\Primary', array(), array(), '', false, false
        );

        $this->_modulesReaderMock = $this->getMock(
            'Magento\Core\Model\Config\Modules\Reader', array(), array(), '', false, false
        );

        $this->_baseConfigMock = $this->getMock(
            'Magento\Core\Model\Config\Base', array(), array(), '', false, false
        );

        $this->_model = new \Magento\Core\Model\Config\Loader(
            $this->_primaryConfigMock,
            $this->_modulesReaderMock
        );
    }

    public function testLoadWithEmptyConfig()
    {
        /** Test load initial xml */
        $this->_baseConfigMock->expects($this->once())->method('getNode')->will($this->returnValue(null));
        $this->_baseConfigMock->expects($this->once())->method('loadString')->with('<config></config>');

        /** Test extends config with primary config values */
        $this->_baseConfigMock->expects($this->once())->method('extend')->with($this->_primaryConfigMock);

        /** Test loading of DB provider specific config files */
        $this->_modulesReaderMock->expects($this->once())
            ->method('loadModulesConfiguration')
            ->with(array('config.xml'), $this->_baseConfigMock);

        /** Test merging of all config data */
        $this->_baseConfigMock->expects($this->once())->method('applyExtends');

        $this->_model->load($this->_baseConfigMock);
    }

    /**
     * @depends testLoadWithEmptyConfig
     */
    public function testLoadWithNotEmptyConfig()
    {
        /** Test load initial xml */
        $this->_baseConfigMock->expects($this->once())->method('getNode')->will($this->returnValue('some value'));
        $this->_baseConfigMock->expects($this->never())->method('loadString');

        $this->_model->load($this->_baseConfigMock);
    }
}
