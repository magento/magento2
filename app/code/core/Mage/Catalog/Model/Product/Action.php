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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Product Mass Action processing model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Action extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Catalog_Model_Resource_Product_Action');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return Mage_Catalog_Model_Resource_Product_Action
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Update attribute values for entity list per store
     *
     * @param array $productIds
     * @param array $attrData
     * @param int $storeId
     * @return Mage_Catalog_Model_Product_Action
     */
    public function updateAttributes($productIds, $attrData, $storeId)
    {
        Mage::dispatchEvent('catalog_product_attribute_update_before', array(
            'attributes_data' => &$attrData,
            'product_ids'   => &$productIds,
            'store_id'      => &$storeId
        ));

        $this->_getResource()->updateAttributes($productIds, $attrData, $storeId);
        $this->setData(array(
            'product_ids'       => array_unique($productIds),
            'attributes_data'   => $attrData,
            'store_id'          => $storeId
        ));

        // register mass action indexer event
        Mage::getSingleton('Mage_Index_Model_Indexer')->processEntityAction(
            $this, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
        );
        return $this;
    }

    /**
     * Update websites for product action
     *
     * allowed types:
     * - add
     * - remove
     *
     * @param array $productIds
     * @param array $websiteIds
     * @param string $type
     */
    public function updateWebsites($productIds, $websiteIds, $type)
    {
        Mage::dispatchEvent('catalog_product_website_update_before', array(
            'website_ids'   => $websiteIds,
            'product_ids'   => $productIds,
            'action'        => $type
        ));

        if ($type == 'add') {
            Mage::getModel('Mage_Catalog_Model_Product_Website')->addProducts($websiteIds, $productIds);
        } else if ($type == 'remove') {
            Mage::getModel('Mage_Catalog_Model_Product_Website')->removeProducts($websiteIds, $productIds);
        }

        $this->setData(array(
            'product_ids' => array_unique($productIds),
            'website_ids' => $websiteIds,
            'action_type' => $type
        ));

        // register mass action indexer event
        Mage::getSingleton('Mage_Index_Model_Indexer')->processEntityAction(
            $this, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
        );

        // add back compatibility system event
        Mage::dispatchEvent('catalog_product_website_update', array(
            'website_ids'   => $websiteIds,
            'product_ids'   => $productIds,
            'action'        => $type
        ));
    }
}
