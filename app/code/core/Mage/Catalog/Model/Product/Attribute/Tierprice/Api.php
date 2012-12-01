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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Product tier price api
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Attribute_Tierprice_Api extends Mage_Catalog_Model_Api_Resource
{
    const ATTRIBUTE_CODE = 'tier_price';

    public function __construct()
    {
        $this->_storeIdSessionField = 'product_store_id';
    }

    public function info($productId, $identifierType = null)
    {
        $product = $this->_initProduct($productId, $identifierType);
        $tierPrices = $product->getData(self::ATTRIBUTE_CODE);

        if (!is_array($tierPrices)) {
            return array();
        }

        $result = array();

        foreach ($tierPrices as $tierPrice) {
            $row = array();
            $row['customer_group_id'] = (empty($tierPrice['all_groups']) ? $tierPrice['cust_group'] : 'all' );
            $row['website']           = ($tierPrice['website_id'] ?
                            Mage::app()->getWebsite($tierPrice['website_id'])->getCode() :
                            'all'
                    );
            $row['qty']               = $tierPrice['price_qty'];
            $row['price']             = $tierPrice['price'];

            $result[] = $row;
        }

        return $result;
    }

    /**
     * Update tier prices of product
     *
     * @param int|string $productId
     * @param array $tierPrices
     * @return boolean
     */
    public function update($productId, $tierPrices, $identifierType = null)
    {
        $product = $this->_initProduct($productId, $identifierType);

        $updatedTierPrices = $this->prepareTierPrices($product, $tierPrices);
        if (is_null($updatedTierPrices)) {
            $this->_fault('data_invalid', Mage::helper('Mage_Catalog_Helper_Data')->__('Invalid Tier Prices'));
        }

        $product->setData(self::ATTRIBUTE_CODE, $updatedTierPrices);
        try {
            /**
             * @todo implement full validation process with errors returning which are ignoring now
             * @todo see Mage_Catalog_Model_Product::validate()
             */
            if (is_array($errors = $product->validate())) {
                $strErrors = array();
                foreach($errors as $code=>$error) {
                    $strErrors[] = ($error === true)? Mage::helper('Mage_Catalog_Helper_Data')->__('Value for "%s" is invalid.', $code) : Mage::helper('Mage_Catalog_Helper_Data')->__('Value for "%s" is invalid: %s', $code, $error);
                }
                $this->_fault('data_invalid', implode("\n", $strErrors));
            }

            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_updated', $e->getMessage());
        }

        return true;
    }

    /**
     *  Prepare tier prices for save
     *
     *  @param      Mage_Catalog_Model_Product $product
     *  @param      array $tierPrices
     *  @return     array
     */
    public function prepareTierPrices($product, $tierPrices = null)
    {
        if (!is_array($tierPrices)) {
            return null;
        }

        if (!is_array($tierPrices)) {
            $this->_fault('data_invalid', Mage::helper('Mage_Catalog_Helper_Data')->__('Invalid Tier Prices'));
        }

        $updateValue = array();

        foreach ($tierPrices as $tierPrice) {
            if (!is_array($tierPrice)
                || !isset($tierPrice['qty'])
                || !isset($tierPrice['price'])) {
                $this->_fault('data_invalid', Mage::helper('Mage_Catalog_Helper_Data')->__('Invalid Tier Prices'));
            }

            if (!isset($tierPrice['website']) || $tierPrice['website'] == 'all') {
                $tierPrice['website'] = 0;
            } else {
                try {
                    $tierPrice['website'] = Mage::app()->getWebsite($tierPrice['website'])->getId();
                } catch (Mage_Core_Exception $e) {
                    $tierPrice['website'] = 0;
                }
            }

            if (intval($tierPrice['website']) > 0 && !in_array($tierPrice['website'], $product->getWebsiteIds())) {
                $this->_fault('data_invalid', Mage::helper('Mage_Catalog_Helper_Data')->__('Invalid tier prices. The product is not associated to the requested website.'));
            }

            if (!isset($tierPrice['customer_group_id'])) {
                $tierPrice['customer_group_id'] = 'all';
            }

            if ($tierPrice['customer_group_id'] == 'all') {
                $tierPrice['customer_group_id'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }

            $updateValue[] = array(
                'website_id' => $tierPrice['website'],
                'cust_group' => $tierPrice['customer_group_id'],
                'price_qty'  => $tierPrice['qty'],
                'price'      => $tierPrice['price']
            );
        }

        return $updateValue;
    }

    /**
     * Retrieve product
     *
     * @param int $productId
     * @param  string $identifierType
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct($productId, $identifierType = null)
    {
        $product = Mage::helper('Mage_Catalog_Helper_Product')->getProduct($productId, $this->_getStoreId(), $identifierType);
        if (!$product->getId()) {
            $this->_fault('product_not_exists');
        }

        return $product;
    }
} // Class Mage_Catalog_Model_Product_Attribute_Tierprice End
