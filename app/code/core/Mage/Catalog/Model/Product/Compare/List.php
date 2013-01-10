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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product Compare List Model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Compare_List extends Varien_Object
{
    /**
     * Add product to Compare List
     *
     * @param int|Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Compare_List
     */
    public function addProduct($product)
    {
        /* @var $item Mage_Catalog_Model_Product_Compare_Item */
        $item = Mage::getModel('Mage_Catalog_Model_Product_Compare_Item');
        $this->_addVisitorToItem($item);
        $item->loadByProduct($product);

        if (!$item->getId()) {
            $item->addProductData($product);
            $item->save();
        }

        return $this;
    }

    /**
     * Add products to compare list
     *
     * @param array $productIds
     * @return Mage_Catalog_Model_Product_Compare_List
     */
    public function addProducts($productIds)
    {
        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                $this->addProduct($productId);
            }
        }
        return $this;
    }

    /**
     * Retrieve Compare Items Collection
     *
     * @return product_compare_item_collection
     */
    public function getItemCollection()
    {
        return Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Compare_Item_Collection');
    }

    /**
     * Remove product from compare list
     *
     * @param int|Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Compare_List
     */
    public function removeProduct($product)
    {
        /* @var $item Mage_Catalog_Model_Product_Compare_Item */
        $item = Mage::getModel('Mage_Catalog_Model_Product_Compare_Item');
        $this->_addVisitorToItem($item);
        $item->loadByProduct($product);

        if ($item->getId()) {
            $item->delete();
        }

        return $this;
    }

    /**
     * Add visitor and customer data to compare item
     *
     * @param Mage_Catalog_Model_Product_Compare_Item $item
     * @return Mage_Catalog_Model_Product_Compare_List
     */
    protected function _addVisitorToItem($item)
    {
        $item->addVisitorId(Mage::getSingleton('Mage_Log_Model_Visitor')->getId());
        if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            $item->addCustomerData(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer());
        }

        return $this;
    }

    /**
     * Check has compare items by visitor/customer
     *
     * @param int $customerId
     * @param int $visitorId
     * @return bool
     */
    public function hasItems($customerId, $visitorId)
    {
        return Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product_Compare_Item')
            ->getCount($customerId, $visitorId);
    }
}
