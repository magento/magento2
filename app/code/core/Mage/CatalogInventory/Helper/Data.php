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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalo
 */
class Mage_CatalogInventory_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SHOW_OUT_OF_STOCK    = 'cataloginventory/options/show_out_of_stock';
    const XML_PATH_ITEM_AUTO_RETURN     = 'cataloginventory/item_options/auto_return';

    /**
     * Error codes, that Catalog Inventory module can set to quote or quote items
     */
    const ERROR_QTY =               1;
    const ERROR_QTY_INCREMENTS =    2;

    /**
     * All product types registry in scope of quantity availability
     *
     * @var array
     */
    protected static $_isQtyTypeIds;

    /**
     * Check if quantity defined for specified product type
     *
     * @param string $productTypeId
     * @return bool
     */
    public function isQty($productTypeId)
    {
        $this->getIsQtyTypeIds();
        if (!isset(self::$_isQtyTypeIds[$productTypeId])) {
            return false;
        }
        return self::$_isQtyTypeIds[$productTypeId];
    }

    /**
     * Get all registered product type ids and if quantity is defined for them
     *
     * @param bool $filter
     * @return array
     */
    public function getIsQtyTypeIds($filter = null)
    {
        if (null === self::$_isQtyTypeIds) {
            self::$_isQtyTypeIds = array();
            $productTypesXml = Mage::getConfig()->getNode('global/catalog/product/type');
            foreach ($productTypesXml->children() as $typeId => $configXml) {
                self::$_isQtyTypeIds[$typeId] = (bool)$configXml->is_qty;
            }
        }
        if (null === $filter) {
            return self::$_isQtyTypeIds;
        }
        $result = self::$_isQtyTypeIds;
        foreach ($result as $key => $value) {
            if ($value !== $filter) {
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * Retrieve inventory item options (used in config)
     *
     * @return array
     */
    public function getConfigItemOptions()
    {
        return array(
            'min_qty',
            'backorders',
            'min_sale_qty',
            'max_sale_qty',
            'notify_stock_qty',
            'manage_stock',
            'enable_qty_increments',
            'qty_increments',
        );
    }

    /**
     * Display out of stock products option
     *
     * @return bool
     */
    public function isShowOutOfStock()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SHOW_OUT_OF_STOCK);
    }

    /**
     * Check if creditmemo items auto return option is enabled
     * @return bool
     */
    public function isAutoReturnEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ITEM_AUTO_RETURN);
    }
}
