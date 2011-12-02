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
 * Catalog Product Flat Helper
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Helper_Product_Flat extends Mage_Core_Helper_Abstract
{
    const XML_PATH_USE_PRODUCT_FLAT          = 'catalog/frontend/flat_catalog_product';
    const XML_NODE_ADD_FILTERABLE_ATTRIBUTES = 'global/catalog/product/flat/add_filterable_attributes';
    const XML_NODE_ADD_CHILD_DATA            = 'global/catalog/product/flat/add_child_data';

    /**
     * Catalog Flat Product index process code
     * 
     * @var string
     */
    const CATALOG_FLAT_PROCESS_CODE = 'catalog_product_flat';
    
    /**
     * Catalog Product Flat index process
     * 
     * @var Mage_Index_Model_Process
     */
    protected $_process;

    /**
     * Catalog Product Flat status by store
     * 
     * @var array
     */
    protected $_isEnabled = array();    
    
    /**
     * Catalog Product Flat Flag object
     *
     * @var Mage_Catalog_Model_Product_Flat_Flag
     */
    protected $_flagObject;

    /**
     * Retrieve Catalog Product Flat Flag object
     *
     * @return Mage_Catalog_Model_Product_Flat_Flag
     */
    public function getFlag()
    {
        if (is_null($this->_flagObject)) {
            $this->_flagObject = Mage::getSingleton('Mage_Catalog_Model_Product_Flat_Flag')
                ->loadSelf();
        }
        return $this->_flagObject;
    }

    /**
     * Check is builded Catalog Product Flat Data
     *
     * @return bool
     */
    public function isBuilt()
    {
        return $this->getFlag()->getIsBuilt();
    }

    /**
     * Check is enable catalog product for store
     *
     * @param mixed $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        $store = Mage::app()->getStore($store);
        if ($store->isAdmin()) {
            return false;
        }
        
        if (!isset($this->_isEnabled[$store->getId()])) {
            if (Mage::getStoreConfigFlag(self::XML_PATH_USE_PRODUCT_FLAT, $store)) {
                $this->_isEnabled[$store->getId()] = $this->getProcess()->getStatus() == Mage_Index_Model_Process::STATUS_PENDING;
            } else {
                $this->_isEnabled[$store->getId()] = false;
            }
        }
        return $this->_isEnabled[$store->getId()];        
    }

    /**
     * Is add filterable attributes to Flat table
     *
     * @return int
     */
    public function isAddFilterableAttributes()
    {
        return intval(Mage::getConfig()->getNode(self::XML_NODE_ADD_FILTERABLE_ATTRIBUTES));
    }

    /**
     * Is add child data to Flat
     *
     * @return int
     */
    public function isAddChildData()
    {
        return intval(Mage::getConfig()->getNode(self::XML_NODE_ADD_CHILD_DATA));
    }

    /**
     * Retrive Catalog Product Flat index process
     * 
     * @return Mage_Index_Model_Process
     */
    public function getProcess()
    {
        if (is_null($this->_process)) {
            $this->_process = Mage::getModel('Mage_Index_Model_Process')
                ->load(self::CATALOG_FLAT_PROCESS_CODE, 'indexer_code');
        }
        return $this->_process;
    }
}
