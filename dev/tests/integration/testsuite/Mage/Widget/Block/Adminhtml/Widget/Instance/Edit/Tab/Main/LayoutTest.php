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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Widget
 */
class Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main_Layout
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = new Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main_Layout(array(
            'widget_instance' => new Mage_Widget_Model_Widget_Instance()
        ));
        $this->_block->setLayout(Mage::app()->getLayout());
    }

    public function testGetLayoutsChooser()
    {
        $actualHtml = $this->_block->getLayoutsChooser();
        $this->assertStringStartsWith('<select ', $actualHtml);
        $this->assertStringEndsWith('</select>', $actualHtml);
        $this->assertContains('id="layout_handle"', $actualHtml);
        $optionCount = substr_count($actualHtml, '<option ');
        $this->assertGreaterThan(1, $optionCount, 'HTML select tag must provide options to choose from.');
        $this->assertEquals($optionCount, substr_count($actualHtml, '</option>'));
    }
}
