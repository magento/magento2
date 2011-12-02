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
 * Catalog product tier price backend attribute model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Attribute_Backend_Tierprice extends Mage_Catalog_Model_Product_Attribute_Backend_Price
{
    /**
     * Website currency codes and rates
     *
     * @var array
     */
    protected $_rates;

    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Backend_Tierprice
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product_Attribute_Backend_Tierprice');
    }

    /**
     * Retrieve websites rates and base currency codes
     *
     * @return array
     */
    public function _getWebsiteRates()
    {
        if (is_null($this->_rates)) {
            $this->_rates = array();
            $baseCurrency = Mage::app()->getBaseCurrencyCode();
            foreach (Mage::app()->getWebsites() as $website) {
                /* @var $website Mage_Core_Model_Website */
                if ($website->getBaseCurrencyCode() != $baseCurrency) {
                    $rate = Mage::getModel('Mage_Directory_Model_Currency')
                        ->load($baseCurrency)
                        ->getRate($website->getBaseCurrencyCode());
                    if (!$rate) {
                        $rate = 1;
                    }
                    $this->_rates[$website->getId()] = array(
                        'code' => $website->getBaseCurrencyCode(),
                        'rate' => $rate
                    );
                } else {
                    $this->_rates[$website->getId()] = array(
                        'code' => $baseCurrency,
                        'rate' => 1
                    );
                }
            }
        }
        return $this->_rates;
    }

    /**
     * Validate tier price data
     *
     * @param Mage_Catalog_Model_Product $object
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function validate($object)
    {
        $attribute  = $this->getAttribute();
        $tiers      = $object->getData($attribute->getName());
        if (empty($tiers)) {
            return true;
        }

        // validate per website
        $duplicates = array();
        foreach ($tiers as $tier) {
            if (!empty($tier['delete'])) {
                continue;
            }
            $compare = join('-', array($tier['website_id'], $tier['cust_group'], $tier['price_qty'] * 1));
            if (isset($duplicates[$compare])) {
                Mage::throwException(
                    Mage::helper('Mage_Catalog_Helper_Data')->__('Duplicate website tier price customer group and quantity.')
                );
            }
            $duplicates[$compare] = true;
        }

        // if attribute scope is website and edit in store view scope
        // add global tier prices for duplicates find
        if (!$attribute->isScopeGlobal() && $object->getStoreId()) {
            $origTierPrices = $object->getOrigData($attribute->getName());
            foreach ($origTierPrices as $tier) {
                if ($tier['website_id'] == 0) {
                    $compare = join('-', array($tier['website_id'], $tier['cust_group'], $tier['price_qty'] * 1));
                    $duplicates[$compare] = true;
                }
            }
        }

        // validate currency
        $baseCurrency = Mage::app()->getBaseCurrencyCode();
        $rates = $this->_getWebsiteRates();
        foreach ($tiers as $tier) {
            if (!empty($tier['delete'])) {
                continue;
            }
            if ($tier['website_id'] == 0) {
                continue;
            }

            $compare = join('-', array($tier['website_id'], $tier['cust_group'], $tier['price_qty']));
            $globalCompare = join('-', array(0, $tier['cust_group'], $tier['price_qty'] * 1));
            $websiteCurrency = $rates[$tier['website_id']]['code'];

            if ($baseCurrency == $websiteCurrency && isset($duplicates[$globalCompare])) {
                Mage::throwException(
                    Mage::helper('Mage_Catalog_Helper_Data')->__('Duplicate website tier price customer group and quantity.')
                );
            }
        }

        return true;
    }

    /**
     * Prepare tier prices data for website
     *
     * @param array $priceData
     * @param string $productTypeId
     * @param int $websiteId
     * @return array
     */
    public function preparePriceData(array $priceData, $productTypeId, $websiteId)
    {
        $rates  = $this->_getWebsiteRates();
        $data   = array();
        $price  = Mage::getSingleton('Mage_Catalog_Model_Product_Type')->priceFactory($productTypeId);
        foreach ($priceData as $v) {
            $key = join('-', array($v['cust_group'], $v['price_qty']));
            if ($v['website_id'] == $websiteId) {
                $data[$key] = $v;
                $data[$key]['website_price'] = $v['price'];
            } else if ($v['website_id'] == 0 && !isset($data[$key])) {
                $data[$key] = $v;
                $data[$key]['website_id'] = $websiteId;
                if ($price->isTierPriceFixed()) {
                    $data[$key]['price'] = $v['price'] * $rates[$websiteId]['rate'];
                    $data[$key]['website_price'] = $v['price'] * $rates[$websiteId]['rate'];
                }
            }
        }

        return $data;
    }

    /**
     * Assign tier prices to product data
     *
     * @param Mage_Catalog_Model_Product $object
     * @return Mage_Catalog_Model_Product_Attribute_Backend_Tierprice
     */
    public function afterLoad($object)
    {
        $storeId   = $object->getStoreId();
        $websiteId = null;
        if ($this->getAttribute()->isScopeGlobal()) {
            $websiteId = 0;
        } else if ($storeId) {
            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        }

        $data = $this->_getResource()->loadPriceData($object->getId(), $websiteId);
        foreach ($data as $k => $v) {
            $data[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $data[$k]['cust_group'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }
        }

        if (!$object->getData('_edit_mode') && $websiteId) {
            $data = $this->preparePriceData($data, $object->getTypeId(), $websiteId);
        }

        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);

        return $this;
    }

    /**
     * After Save Attribute manipulation
     *
     * @param Mage_Catalog_Model_Product $object
     * @return Mage_Catalog_Model_Product_Attribute_Backend_Tierprice
     */
    public function afterSave($object)
    {
        $websiteId  = Mage::app()->getStore($object->getStoreId())->getWebsiteId();
        $isGlobal   = $this->getAttribute()->isScopeGlobal() || $websiteId == 0;

        $tierPrices = $object->getData($this->getAttribute()->getName());
        if (empty($tierPrices)) {
            if ($isGlobal) {
                $this->_getResource()->deletePriceData($object->getId());
            } else {
                $this->_getResource()->deletePriceData($object->getId(), $websiteId);
            }
            return $this;
        }

        $old = array();
        $new = array();

        // prepare original data for compare
        $origTierPrices = $object->getOrigData($this->getAttribute()->getName());
        if (!is_array($origTierPrices)) {
            $origTierPrices = array();
        }
        foreach ($origTierPrices as $data) {
            if ($data['website_id'] > 0 || ($data['website_id'] == '0' && $isGlobal)) {
                $key = join('-', array($data['website_id'], $data['cust_group'], $data['price_qty'] * 1));
                $old[$key] = $data;
            }
        }

        // prepare data for save
        foreach ($tierPrices as $data) {
            if (empty($data['price_qty']) || !isset($data['cust_group']) || !empty($data['delete'])) {
                continue;
            }
            if ($this->getAttribute()->isScopeGlobal() && $data['website_id'] > 0) {
                continue;
            }
            if (!$isGlobal && (int)$data['website_id'] == 0) {
                continue;
            }

            $key = join('-', array($data['website_id'], $data['cust_group'], $data['price_qty'] * 1));

            $useForAllGroups = $data['cust_group'] == Mage_Customer_Model_Group::CUST_GROUP_ALL;
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;

            $new[$key] = array(
                'website_id'        => $data['website_id'],
                'all_groups'        => $useForAllGroups ? 1 : 0,
                'customer_group_id' => $customerGroupId,
                'qty'               => $data['price_qty'],
                'value'             => $data['price'],
            );
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        $isChanged  = false;
        $productId  = $object->getId();

        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->_getResource()->deletePriceData($productId, null, $data['price_id']);
                $isChanged = true;
            }
        }

        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new Varien_Object($data);
                $price->setEntityId($productId);
                $this->_getResource()->savePriceData($price);

                $isChanged = true;
            }
        }

        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['price'] != $v['value']) {
                    $price = new Varien_Object(array(
                        'value_id'  => $old[$k]['price_id'],
                        'value'     => $v['value']
                    ));
                    $this->_getResource()->savePriceData($price);

                    $isChanged = true;
                }
            }
        }

        if ($isChanged) {
            $valueChangedKey = $this->getAttribute()->getName() . '_changed';
            $object->setData($valueChangedKey, 1);
        }

        return $this;
    }
}
