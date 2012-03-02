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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Links block
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_Block_Links extends Mage_Page_Block_Template_Links_Block
{
    /**
     * Position in link list
     * @var int
     */
    protected $_position = 30;

    /**
     * Set link title, label and url
     */
    public function __construct()
    {
        parent::__construct();
        $this->initLinkProperties();
    }

    /**
     * Define label, title and url for wishlist link
     */
    public function initLinkProperties()
    {
        if ($this->helper('Mage_Wishlist_Helper_Data')->isAllow()) {
            $count = $this->getItemCount() ? $this->getItemCount() : $this->helper('Mage_Wishlist_Helper_Data')->getItemCount();
            if ($count > 1) {
                $text = $this->__('My Wishlist (%d items)', $count);
            } else if ($count == 1) {
                $text = $this->__('My Wishlist (%d item)', $count);
            } else {
                $text = $this->__('My Wishlist');
            }
            $this->_label = $text;
            $this->_title = $text;
            $this->_url = $this->getUrl('wishlist');
        }
    }
}
