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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Links block
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Block_Links extends Mage_Core_Block_Template
{
    /**
     * Add shopping cart link to parent block
     *
     * @return Mage_Checkout_Block_Links
     */
    public function addCartLink()
    {
        /** @var $parentBlock Mage_Page_Block_Template_Links */
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && Mage::helper('Mage_Core_Helper_Data')->isModuleOutputEnabled('Mage_Checkout')) {
            $count = $this->getSummaryQty() ? $this->getSummaryQty()
                : $this->helper('Mage_Checkout_Helper_Cart')->getSummaryCount();
            if ($count == 1) {
                $text = $this->__('My Cart (%s item)', $count);
            } elseif ($count > 0) {
                $text = $this->__('My Cart (%s items)', $count);
            } else {
                $text = $this->__('My Cart');
            }

            $this->removeParentCartLink();
            $parentBlock->addLink($text, 'checkout/cart', $text, true, array(), 50, null, 'class="top-link-cart"');
        }
        return $this;
    }

    /**
     * Remove checkout/cart link from parent block
     */
    public function removeParentCartLink()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock and $parentBlock instanceof Mage_Page_Block_Template_Links) {
            $parentBlock->removeLinkByUrl($this->getUrl('checkout/cart'));
        }
    }

    /**
     * Add link on checkout page to parent block
     *
     * @return Mage_Checkout_Block_Links
     */
    public function addCheckoutLink()
    {
        if (!$this->helper('Mage_Checkout_Helper_Data')->canOnepageCheckout()) {
            return $this;
        }

        /** @var $parentBlock Mage_Page_Block_Template_Links */
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && Mage::helper('Mage_Core_Helper_Data')->isModuleOutputEnabled('Mage_Checkout')) {
            $text = $this->__('Checkout');
            $parentBlock->addLink($text, 'checkout', $text, true, array('_secure' => true), 60, null,
                'class="top-link-checkout"'
            );
        }
        return $this;
    }
}
