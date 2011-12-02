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
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Downloadable order item render block
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Block_Sales_Order_Item_Renderer_Downloadable extends Mage_Sales_Block_Order_Item_Renderer_Default
{
    protected $_purchasedLinks = null;

    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getLinks()
    {
            $this->_purchasedLinks = Mage::getModel('Mage_Downloadable_Model_Link_Purchased')
                ->load($this->getOrderItem()->getOrder()->getId(), 'order_id');
            $purchasedItems = Mage::getModel('Mage_Downloadable_Model_Link_Purchased_Item')->getCollection()
                ->addFieldToFilter('order_item_id', $this->getOrderItem()->getId());
            $this->_purchasedLinks->setPurchasedItems($purchasedItems);

        return $this->_purchasedLinks;
    }

    public function getLinksTitle()
    {
        if ($this->_purchasedLinks->getLinkSectionTitle()) {
            return $this->_purchasedLinks->getLinkSectionTitle();
        }
        return Mage::getStoreConfig(Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE);
    }

}
