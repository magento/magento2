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
 * @package     Mage_CatalogInventory
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog inventory api
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogInventory_Model_Stock_Item_Api extends Mage_Catalog_Model_Api_Resource
{
    public function __construct()
    {
        $this->_storeIdSessionField = 'product_store_id';
    }

    public function items($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $product = Mage::getModel('Mage_Catalog_Model_Product');

        foreach ($productIds as &$productId) {
            if ($newId = $product->getIdBySku($productId)) {
                $productId = $newId;
            }
        }

        $collection = Mage::getModel('Mage_Catalog_Model_Product')
            ->getCollection()
            ->setFlag('require_stock_items', true)
            ->addFieldToFilter('entity_id', array('in'=>$productIds));

        $result = array();

        foreach ($collection as $product) {
            if ($product->getStockItem()) {
                $result[] = array(
                    'product_id'    => $product->getId(),
                    'sku'           => $product->getSku(),
                    'qty'           => $product->getStockItem()->getQty(),
                    'is_in_stock'   => $product->getStockItem()->getIsInStock()
                );
            }
        }

        return $result;
    }
} // Class Mage_CatalogInventory_Model_Stock_Item_Api End
