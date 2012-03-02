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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog inventory api V2
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogInventory_Model_Stock_Item_Api_V2 extends Mage_CatalogInventory_Model_Stock_Item_Api
{
    public function update($productId, $data)
    {
        $product = Mage::getModel('Mage_Catalog_Model_Product');

        if ($newId = $product->getIdBySku($productId)) {
            $productId = $newId;
        }

        $product->setStoreId($this->_getStoreId())
            ->load($productId);

        if (!$product->getId()) {
            $this->_fault('not_exists');
        }

        if (!$stockData = $product->getStockData()) {
            $stockData = array();
        }

        if (isset($data->qty)) {
            $stockData['qty'] = $data->qty;
        }

        if (isset($data->is_in_stock)) {
            $stockData['is_in_stock'] = $data->is_in_stock;
        }

        if (isset($data->manage_stock)) {
            $stockData['manage_stock'] = $data->manage_stock;
        }

        if (isset($data->use_config_manage_stock)) {
            $stockData['use_config_manage_stock'] = $data->use_config_manage_stock;
        }

        $product->setStockData($stockData);

        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_updated', $e->getMessage());
        }

        return true;
    }
}
