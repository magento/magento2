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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Obtain all carts contents for specified client
 *
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Carts extends Mage_Adminhtml_Block_Template
{
    /**
     * Add shopping cart grid of each website
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Carts
     */
    protected function _prepareLayout()
    {
        $sharedWebsiteIds = Mage::registry('current_customer')->getSharedWebsiteIds();
        $isShared = count($sharedWebsiteIds) > 1;
        foreach ($sharedWebsiteIds as $websiteId) {
            $blockName = 'customer_cart_' . $websiteId;
            $block = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Customer_Edit_Tab_Cart',
                $blockName, array('website_id' => $websiteId));
            if ($isShared) {
                $block->setCartHeader($this->__('Shopping Cart from %s', Mage::app()->getWebsite($websiteId)->getName()));
            }
            $this->setChild($blockName, $block);
        }
        return parent::_prepareLayout();
    }

    /**
     * Just get child blocks html
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent('adminhtml_block_html_before', array('block' => $this));
        return $this->getChildHtml();
    }
}
