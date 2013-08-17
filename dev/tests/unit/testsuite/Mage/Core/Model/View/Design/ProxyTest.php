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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_View_Design_ProxyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_View_Design_Proxy
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_View_DesignInterface
     */
    protected $_viewDesign;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento_ObjectManager');
        $this->_viewDesign = $this->getMock('Mage_Core_Model_View_DesignInterface');
        $this->_objectManager->expects($this->once())
            ->method('get')
            ->with('Mage_Core_Model_View_Design')
            ->will($this->returnValue($this->_viewDesign));
        $this->_model = new Mage_Core_Model_View_Design_Proxy($this->_objectManager);
    }

    protected function tearDown()
    {
        $this->_objectManager = null;
        $this->_model = null;
        $this->_viewDesign = null;
    }

    public function testGetDesignParams()
    {
        $this->_viewDesign->expects($this->once())
            ->method('getDesignParams')
            ->will($this->returnValue('return value'));
        $this->assertSame('return value', $this->_model->getDesignParams());
    }
}
