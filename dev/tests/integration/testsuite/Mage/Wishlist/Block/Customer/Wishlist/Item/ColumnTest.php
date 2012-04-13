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
 * @package     Mage_Wishlist
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Wishlist
 */
class Mage_Wishlist_Block_Customer_Wishlist_Item_ColumnTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout = null;

    /**
     * @var Mage_Wishlist_Block_Customer_Wishlist_Item_Column
     */
    protected $_block = null;

    protected function setUp()
    {
        $this->_layout = new Mage_Core_Model_Layout;
        $this->_block = $this->_layout->addBlock('Mage_Wishlist_Block_Customer_Wishlist_Item_Column', 'test');
        $this->_layout->addBlock('Mage_Core_Block_Text', 'child', 'test');

    }

    public function testToHtml()
    {
        $item = new StdClass;
        $this->_block->setItem($item);
        $this->_block->toHtml();
        $this->assertSame($item, $this->_layout->getBlock('child')->getItem());
    }

    public function testGetJs()
    {
        $expected = uniqid();
        $this->_layout->getBlock('child')->setJs($expected);
        $this->assertEquals($expected, $this->_block->getJs());
    }
}
