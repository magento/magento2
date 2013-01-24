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
 * @package     Mage_Widget
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Layout|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_block;

    protected $_blockInjections = array(
        'Mage_Core_Controller_Request_Http',
        'Mage_Core_Model_Layout',
        'Mage_Core_Model_Event_Manager',
        'Mage_Backend_Model_Url',
        'Mage_Core_Model_Translate',
        'Mage_Core_Model_Cache',
        'Mage_Core_Model_Design_Package',
        'Mage_Core_Model_Session',
        'Mage_Core_Model_Store_Config',
        'Mage_Core_Controller_Varien_Front',
        'Mage_Core_Model_Factory_Helper'
    );

    protected function setUp()
    {
        $layoutUtility = new Mage_Core_Utility_Layout($this);
        $pageTypesFixture = __DIR__ . '/_files/_page_types_with_containers.xml';
        $args = array_merge($this->_prepareConstructorArguments(), array(array(
            'name'  => 'page_type',
            'id'    => 'page_types_select',
            'class' => 'page-types-select',
            'title' => 'Page Types Select',
        )));
        $this->_block = $this->getMock(
            'Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Layout',
            array('_getLayoutMerge'), $args
        );
        $this->_block
            ->expects($this->any())
            ->method('_getLayoutMerge')
            ->will($this->returnValue($layoutUtility->getLayoutUpdateFromFixture(
            $pageTypesFixture,
            $layoutUtility->getLayoutDependencies()
        )))
        ;
    }

    protected function tearDown()
    {
        $this->_block = null;
    }

    public function testToHtml()
    {
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/_files/page_types_select.html', $this->_block->toHtml());
    }
    /**
     * List of block constructor arguments
     *
     * @return array
     */
    protected function _prepareConstructorArguments()
    {
        $arguments = array();
        foreach ($this->_blockInjections as $injectionClass) {
            $arguments[] = Mage::getModel($injectionClass);
        }
        return $arguments;
    }
}
