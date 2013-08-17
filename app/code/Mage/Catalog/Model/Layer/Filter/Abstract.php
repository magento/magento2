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
 * Layer category filter abstract model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Catalog_Model_Layer_Filter_Abstract extends Varien_Object
{
    /**
     * Request variable name with filter value
     *
     * @var string
     */
    protected $_requestVar;

    /**
     * Array of filter items
     *
     * @var array
     */
    protected $_items;

    /**
     * Set request variable name which is used for apply filter
     *
     * @param   string $varName
     * @return  Mage_Catalog_Model_Layer_Filter_Abstract
     */
    public function setRequestVar($varName)
    {
        $this->_requestVar = $varName;
        return $this;
    }

    /**
     * Get request variable name which is used for apply filter
     *
     * @return string
     */
    public function getRequestVar()
    {
        return $this->_requestVar;
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed
     */
    public function getResetValue()
    {
        return null;
    }

    /**
     * Retrieve filter value for Clear All Items filter state
     *
     * @return mixed
     */
    public function getCleanValue()
    {
        return null;
    }

    /**
     * Apply filter to collection
     *
     * @param  Zend_Controller_Request_Abstract $request
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        return $this;
    }

    /**
     * Get fiter items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return count($this->getItems());
    }

    /**
     * Get all filter items
     *
     * @return array
     */
    public function getItems()
    {
        if (is_null($this->_items)) {
            $this->_initItems();
        }
        return $this->_items;
    }

    /**
     * Get data array for building filter items
     *
     * result array should have next structure:
     * array(
     *      $index => array(
     *          'label' => $label,
     *          'value' => $value,
     *          'count' => $count
     *      )
     * )
     *
     * @return array
     */
    protected function _getItemsData()
    {
        return array();
    }

    /**
     * Initialize filter items
     *
     * @return  Mage_Catalog_Model_Layer_Filter_Abstract
     */
    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items=array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItem(
                $itemData['label'],
                $itemData['value'],
                $itemData['count']
            );
        }
        $this->_items = $items;
        return $this;
    }


    /**
     * Retrieve layer object
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        $layer = $this->_getData('layer');
        if (is_null($layer)) {
            $layer = Mage::getSingleton('Mage_Catalog_Model_Layer');
            $this->setData('layer', $layer);
        }
        return $layer;
    }

    /**
     * Create filter item object
     *
     * @param   string $label
     * @param   mixed $value
     * @param   int $count
     * @return  Mage_Catalog_Model_Layer_Filter_Item
     */
    protected function _createItem($label, $value, $count=0)
    {
        return Mage::getModel('Mage_Catalog_Model_Layer_Filter_Item')
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count);
    }

    /**
     * Get all product ids from from collection with applied filters
     *
     * @return array
     */
    protected function _getFilterEntityIds()
    {
        return $this->getLayer()->getProductCollection()->getAllIdsCache();
    }

    /**
     * Get product collection select object with applied filters
     *
     * @return Varien_Db_Select
     */
    protected function _getBaseCollectionSql()
    {
        return $this->getLayer()->getProductCollection()->getSelect();
    }

    /**
     * Set attribute model to filter
     *
     * @param   Mage_Eav_Model_Entity_Attribute $attribute
     * @return  Mage_Catalog_Model_Layer_Filter_Abstract
     */
    public function setAttributeModel($attribute)
    {
        $this->setRequestVar($attribute->getAttributeCode());
        $this->setData('attribute_model', $attribute);
        return $this;
    }

    /**
     * Get attribute model associated with filter
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttributeModel()
    {
        $attribute = $this->getData('attribute_model');
        if (is_null($attribute)) {
            Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('The attribute model is not defined.'));
        }
        return $attribute;
    }

    /**
     * Get filter text label
     *
     * @return string
     */
    public function getName()
    {
        return $this->getAttributeModel()->getStoreLabel();
    }

    /**
     * Retrieve current store id scope
     *
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->_getData('store_id');
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * Set store id scope
     *
     * @param int $storeId
     * @return Mage_Catalog_Model_Layer_Filter_Abstract
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * Retrieve Website ID scope
     *
     * @return int
     */
    public function getWebsiteId()
    {
        $websiteId = $this->_getData('website_id');
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }
        return $websiteId;
    }

    /**
     * Set Website ID scope
     *
     * @param int $websiteId
     * @return Mage_Catalog_Model_Layer_Filter_Abstract
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData('website_id', $websiteId);
    }

    /**
     * Clear current element link text, for example 'Clear Price'
     *
     * @return false|string
     */
    public function getClearLinkText()
    {
        return false;
    }
}
