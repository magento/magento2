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
 * @package     Mage_Weee
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Weee_Model_Attribute_Backend_Weee_Tax extends Mage_Catalog_Model_Product_Attribute_Backend_Price
{
    public static function getBackendModelName()
    {
        return 'Mage_Weee_Model_Attribute_Backend_Weee_Tax';
    }
    /**
     * Retrieve resource model
     *
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Backend_Weee
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('Mage_Weee_Model_Resource_Attribute_Backend_Weee_Tax');
    }

    /**
     * Validate data
     *
     * @param   Mage_Catalog_Model_Product $object
     * @return  this
     */
    public function validate($object)
    {
        $taxes = $object->getData($this->getAttribute()->getName());
        if (empty($taxes)) {
            return $this;
        }
        $dup = array();

        foreach ($taxes as $tax) {
            if (!empty($tax['delete'])) {
                continue;
            }

            $state = isset($tax['state']) ? $tax['state'] : '*';
            $key1 = implode('-', array($tax['website_id'], $tax['country'], $state));

            if (!empty($dup[$key1])) {
                Mage::throwException(
                    Mage::helper('Mage_Catalog_Helper_Data')->__('We found a duplicate website, country, and state tax.')
                );
            }
            $dup[$key1] = 1;
        }
        return $this;
    }

    /**
     * Assign WEEE taxes to product data
     *
     * @param   Mage_Catalog_Model_Product $object
     * @return  Mage_Catalog_Model_Product_Attribute_Backend_Weee
     */
    public function afterLoad($object)
    {
        $data = $this->_getResource()->loadProductData($object, $this->getAttribute());

        foreach ($data as $i=>$row) {
            if ($data[$i]['website_id'] == 0) {
                $rate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::app()->getBaseCurrencyCode());
                if ($rate) {
                    $data[$i]['website_value'] = $data[$i]['value']/$rate;
                } else {
                    unset($data[$i]);
                }
            } else {
                $data[$i]['website_value'] = $data[$i]['value'];
            }

        }
        $object->setData($this->getAttribute()->getName(), $data);
        return $this;
    }

    public function afterSave($object)
    {
        $orig = $object->getOrigData($this->getAttribute()->getName());
        $current = $object->getData($this->getAttribute()->getName());
        if ($orig == $current) {
            return $this;
        }

        $this->_getResource()->deleteProductData($object, $this->getAttribute());
        $taxes = $object->getData($this->getAttribute()->getName());

        if (!is_array($taxes)) {
            return $this;
        }

        foreach ($taxes as $tax) {
            if (empty($tax['price']) || empty($tax['country']) || !empty($tax['delete'])) {
                continue;
            }

            if (isset($tax['state']) && $tax['state']) {
                $state = $tax['state'];
            } else {
                $state = '*';
            }

            $data = array();
            $data['website_id']   = $tax['website_id'];
            $data['country']      = $tax['country'];
            $data['state']        = $state;
            $data['value']        = $tax['price'];
            $data['attribute_id'] = $this->getAttribute()->getId();

            $this->_getResource()->insertProductData($object, $data);
        }

        return $this;
    }

    public function afterDelete($object)
    {
        $this->_getResource()->deleteProductData($object, $this->getAttribute());
        return $this;
    }

    public function getTable()
    {
        return $this->_getResource()->getTable('weee_tax');
    }
}

