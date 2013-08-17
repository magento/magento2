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
 * Catalog inventory api V2
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_CatalogInventory_Model_Stock_Item_Api_V2 extends Mage_CatalogInventory_Model_Stock_Item_Api
{
    /**
     * Update product stock data
     *
     * @param int   $productId
     * @param array $data
     * @return bool
     */
    public function update($productId, $data)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $productId = $product->getIdBySku($productId) ?: $productId;

        $product->setStoreId($this->_getStoreId())
            ->load($productId);

        if (!$product->getId()) {
            $this->_fault('not_exists');
        }

        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $product->getStockItem();
        $stockData = array_replace($stockItem->getData(), (array)$data);
        $stockItem->setData($stockData);

        try {
            $stockItem->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_updated', $e->getMessage());
        }

        return true;
    }

    /**
     * Update stock data of multiple products at once
     *
     * @param array $productIds
     * @param array $productsData
     * @return boolean
     */
    public function multiUpdate($productIds, $productsData)
    {
        if (count($productIds) != count($productsData)) {
            $this->_fault('multi_update_not_match');
        }

        $productsData = (array)$productsData;

        foreach ($productIds as $index => $productId) {
            $this->update($productId, $productsData[$index]);
        }

        return true;
    }
}
