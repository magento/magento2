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
 * @category    Mage
 * @package     Mage_Tag
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Tag_Block_Product_ResultTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Tag_Block_Product_Result
     */
    protected $_block = null;

    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout = null;

    /**
     * @var Mage_Core_Block_Text
     */
    protected $_child = null;

    public static function setUpBeforeClass()
    {
        Mage::register('current_tag', new Varien_Object(array('id' => uniqid())));
    }

    protected function setUp()
    {
        $this->_block = new Mage_Tag_Block_Product_Result;
        $this->_layout = new Mage_Core_Model_Layout;
        $this->_layout->addBlock('Mage_Core_Block_Text', 'root');
        $this->_layout->addBlock('Mage_Core_Block_Text', 'head');
        $this->_layout->addBlock($this->_block, 'test');
        $this->_child = new Mage_Core_Block_Text;
        $this->_layout->addBlock($this->_child, 'search_result_list', 'test');
    }

    public function testSetListOrders()
    {
        $this->assertEmpty($this->_child->getData('available_orders'));
        $this->_block->setListOrders();
        $this->assertNotEmpty($this->_child->getData('available_orders'));
    }

    public function testSetListModes()
    {
        $this->assertEmpty($this->_child->getData('modes'));
        $this->_block->setListModes();
        $this->assertNotEmpty($this->_child->getData('modes'));
    }

    public function testSetListCollection()
    {
        $this->assertEmpty($this->_child->getData('collection'));
        $this->_block->setListCollection();
        $this->assertNotEmpty($this->_child->getData('collection'));
    }
}
