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
 * @package     Mage_Wishlist
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Wishlist_IndexControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * Verify wishlist view action
     *
     * The following is verified:
     * - Mage_Wishlist_Model_Resource_Item_Collection
     * - Mage_Wishlist_Block_Customer_Wishlist
     * - Mage_Wishlist_Block_Customer_Wishlist_Items
     * - Mage_Wishlist_Block_Customer_Wishlist_Item_Column
     * - Mage_Wishlist_Block_Customer_Wishlist_Item_Column_Cart
     * - Mage_Wishlist_Block_Customer_Wishlist_Item_Column_Comment
     * - Mage_Wishlist_Block_Customer_Wishlist_Button
     * - that Mage_Wishlist_Block_Customer_Wishlist_Item_Options doesn't throw a fatal error
     *
     * @magentoDataFixture Mage/Wishlist/_files/wishlist.php
     */
    public function testItemColumnBlock()
    {
        $session = new Mage_Customer_Model_Session;
        $session->login('customer@example.com', 'password');
        $this->dispatch('wishlist/index/index');
        $body = $this->getResponse()->getBody();
        $this->assertStringMatchesFormat('%A<img src="%Asmall_image.jpg" %A alt="Simple Product"%A/>%A', $body);
        $this->assertStringMatchesFormat('%Afunction addWItemToCart(itemId)%A', $body);
        $this->assertStringMatchesFormat('%Aonclick="addWItemToCart(%d);"%A', $body);
        $this->assertStringMatchesFormat('%A<textarea name="description[%d]"%A', $body);
        $this->assertStringMatchesFormat('%A<button%Aonclick="addAllWItemsToCart()"%A', $body);
    }
}
