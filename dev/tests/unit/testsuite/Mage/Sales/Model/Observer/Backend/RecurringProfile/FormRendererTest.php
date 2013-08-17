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

class Mage_Sales_Model_Observer_Backend_RecurringProfile_FormRendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Sales_Model_Observer_Backend_RecurringProfile_FormRenderer
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_blockFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observerMock;

    public function setUp()
    {
        $this->_blockFactoryMock = $this->getMock(
            'Mage_Core_Model_BlockFactory', array('createBlock'), array(), '', false
        );
        $this->_observerMock = $this->getMock('Varien_Event_Observer', array(), array(), '', false);
        $this->_model = new Mage_Sales_Model_Observer_Backend_RecurringProfile_FormRenderer(
            $this->_blockFactoryMock
        );
    }

    public function testRender()
    {
        $blockMock = $this->getMock(
            'Mage_Core_Block', array(
                'setNameInLayout', 'setParentElement', 'setProductEntity', 'toHtml', 'addFieldMap',
                'addFieldDependence', 'addConfigOptions'
            )
        );
        $map = array(
            array('Mage_Sales_Block_Adminhtml_Recurring_Profile_Edit_Form', array(), $blockMock),
            array('Mage_Backend_Block_Widget_Form_Element_Dependence', array(), $blockMock)

        );
        $event = $this->getMock(
            'Varien_Event', array('getProductElement', 'getProduct', 'getResult'), array(), '', false
        );
        $this->_observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($event));
        $profileElement = $this->getMock('Varien_Data_Form_Element_Abstract', array(), array(), '', false);
        $event->expects($this->once())->method('getProductElement')->will($this->returnValue($profileElement));
        $product = $this->getMock('Mage_Catalog_Model_Product', array(), array(), '', false);
        $event->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $this->_blockFactoryMock->expects($this->any())->method('createBlock')->will($this->returnValueMap($map));
        $blockMock->expects($this->any())->method('setNameInLayout');
        $blockMock->expects($this->once())->method('setParentElement')->with($profileElement);
        $blockMock->expects($this->once())->method('setProductEntity')->with($product);
        $blockMock->expects($this->exactly(2))->method('toHtml')->will($this->returnValue('html'));
        $blockMock->expects($this->once())->method('addConfigOptions')->with(array('levels_up' => 2));
        $result = new StdClass();
        $event->expects($this->once())->method('getResult')->will($this->returnValue($result));
        $this->_model->render($this->_observerMock);
        $this->assertEquals('htmlhtml', $result->output);
    }
}
