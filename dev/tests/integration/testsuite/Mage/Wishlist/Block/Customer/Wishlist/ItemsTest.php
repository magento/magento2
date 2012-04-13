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
class Mage_Wishlist_Block_Customer_Wishlist_ItemsTest extends PHPUnit_Framework_TestCase
{
    public function testGetColumns()
    {
        $layout = new Mage_Core_Model_Layout;
        $block = $layout->addBlock('Mage_Wishlist_Block_Customer_Wishlist_Items', 'test');
        $child = $this->getMock('Mage_Core_Block_Text', array('isEnabled'));
        $child->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true))
        ;
        $layout->addBlock($child, 'child', 'test');
        $this->assertSame(array($child), $block->getColumns());
    }
}
