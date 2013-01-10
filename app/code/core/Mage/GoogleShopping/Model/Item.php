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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Content Item Types Model
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Model_Item extends Mage_Core_Model_Abstract
{
    /**
     * Regestry keys for caching attributes and types
     *
     * @var string
     */
    const TYPES_REGISTRY_KEY = 'gcontent_types_registry';

    /**
     * Service Item Instance
     *
     * @var Mage_GoogleShopping_Model_Service_Item
     */
    protected $_serviceItem = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mage_GoogleShopping_Model_Resource_Item');
    }

    /**
     * Return Service Item Instance
     *
     * @return Mage_GoogleShopping_Model_Service_Item
     */
    public function getServiceItem()
    {
        if (is_null($this->_serviceItem)) {
            $this->_serviceItem = Mage::getModel('Mage_GoogleShopping_Model_Service_Item')
                ->setStoreId($this->getStoreId());
        }
        return $this->_serviceItem;
    }

    /**
     * Set Service Item Instance
     *
     * @param Mage_GoogleShopping_Model_Service_Item $service
     * @return Mage_GoogleShopping_Model_Item
     */
    public function setServiceItem($service)
    {
        $this->_serviceItem = $service;
        return $this;
    }

    /**
     * Target Country
     *
     * @return string Two-letters country ISO code
     */
    public function getTargetCountry()
    {
        return Mage::getSingleton('Mage_GoogleShopping_Model_Config')->getTargetCountry($this->getStoreId());
    }

    /**
     * Save item to Google Content
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_GoogleShopping_Model_Item
     */
    public function insertItem(Mage_Catalog_Model_Product $product)
    {
        $this->setProduct($product);
        $this->getServiceItem()
            ->insert($this);
        $this->setTypeId($this->getType()->getTypeId());

        return $this;
    }

    /**
     * Update Item data
     *
     * @return Mage_GoogleShopping_Model_Item
     */
    public function updateItem()
    {
        if ($this->getId()) {
            $this->getServiceItem()
                ->update($this);
        }
        return $this;
    }

    /**
     * Delete Item from Google Content
     *
     * @return Mage_GoogleShopping_Model_Item
     */
    public function deleteItem()
    {
        $this->getServiceItem()->delete($this);
        return $this;
    }

    /**
     * Load Item Model by Product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_GoogleShopping_Model_Item
     */
    public function loadByProduct($product)
    {
        $this->setProduct($product);
        $this->getResource()->loadByProduct($this);
        return $this;
    }

    /**
     * Return Google Content Item Type Model for current Item
     *
     * @return Mage_GoogleShopping_Model_Type
     */
    public function getType()
    {
        $attributeSetId = $this->getProduct()->getAttributeSetId();
        $targetCountry = $this->getTargetCountry();

        $registry = Mage::registry(self::TYPES_REGISTRY_KEY);
        if (is_array($registry) && isset($registry[$attributeSetId][$targetCountry])) {
            return $registry[$attributeSetId][$targetCountry];
        }

        $type = Mage::getModel('Mage_GoogleShopping_Model_Type')
            ->loadByAttributeSetId($attributeSetId, $targetCountry);

        $registry[$attributeSetId][$targetCountry] = $type;
        Mage::unregister(self::TYPES_REGISTRY_KEY);
        Mage::register(self::TYPES_REGISTRY_KEY, $registry);

        return $type;
    }

    /**
     * Product Getter. Load product if not exist.
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (is_null($this->getData('product')) && !is_null($this->getProductId())) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStoreId($this->getStoreId())
                ->load($this->getProductId());
            $this->setData('product', $product);
        }

        return $this->getData('product');
    }

    /**
     * Product Setter.
     *
     * @param Mage_Catalog_Model_Product
     * @return Mage_GoogleShopping_Model_Item
     */
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->setData('product', $product);
        $this->setProductId($product->getId());
        $this->setStoreId($product->getStoreId());

        return $this;
    }
}
