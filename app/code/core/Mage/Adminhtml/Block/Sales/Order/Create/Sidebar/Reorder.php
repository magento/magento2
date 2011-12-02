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
 * Adminhtml sales order create sidebar cart block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Sidebar_Reorder extends Mage_Adminhtml_Block_Sales_Order_Create_Sidebar_Abstract
{

    /**
     * Storage action on selected item
     *
     * @var string
     */
    protected $_sidebarStorageAction = 'add_order_item';

    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_sidebar_reorder');
        $this->setDataId('reorder');
    }


    public function getHeaderText()
    {
        return Mage::helper('Mage_Sales_Helper_Data')->__('Last Ordered Items');
    }

    /**
     * Retrieve last order on current website
     *
     * @return Mage_Sales_Model_Order|false
     */
    public function getLastOrder()
    {
        $storeIds = $this->getQuote()->getStore()->getWebsite()->getStoreIds();
        $collection = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Collection')
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addFieldToFilter('store_id', array('in' => $storeIds))
            ->setOrder('created_at', 'desc')
            ->setPageSize(1)
            ->load();
        foreach ($collection as $order) {
            return $order;
        }

        return false;
    }
    /**
     * Retrieve item collection
     *
     * @return mixed
     */
    public function getItemCollection()
    {
        if ($order = $this->getLastOrder()) {
            $items = array();
            foreach ($order->getItemsCollection() as $item) {
                if (!$item->getParentItem()) {
                    $items[] = $item;
                }
            }
            return $items;
        }
        return false;
    }

    public function canDisplayItemQty()
    {
        return false;
    }

    public function canRemoveItems()
    {
        return false;
    }

    public function canDisplayPrice()
    {
        return false;
    }

    /**
     * Retrieve identifier of block item
     *
     * @param Varien_Object $item
     * @return int
     */
    public function getIdentifierId($item)
    {
        return $item->getId();
    }
}
