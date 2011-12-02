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
 * Catalog Product tier price api V2
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2 extends Mage_Catalog_Model_Product_Attribute_Tierprice_Api
{
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

        $updateValue = array();

        foreach ($tierPrices as $tierPrice) {
            if (!is_object($tierPrice)
                || !isset($tierPrice->qty)
                || !isset($tierPrice->price)) {
                $this->_fault('data_invalid', Mage::helper('Mage_Catalog_Helper_Data')->__('Invalid Tier Prices'));
            }

            if (!isset($tierPrice->website) || $tierPrice->website == 'all') {
                $tierPrice->website = 0;
            } else {
                try {
                    $tierPrice->website = Mage::app()->getWebsite($tierPrice->website)->getId();
                } catch (Mage_Core_Exception $e) {
                    $tierPrice->website = 0;
                }
            }

            if (intval($tierPrice->website) > 0 && !in_array($tierPrice->website, $product->getWebsiteIds())) {
                $this->_fault('data_invalid', Mage::helper('Mage_Catalog_Helper_Data')->__('Invalid tier prices. The product is not associated to the requested website.'));
            }

            if (!isset($tierPrice->customer_group_id)) {
                $tierPrice->customer_group_id = 'all';
            }

            if ($tierPrice->customer_group_id == 'all') {
                $tierPrice->customer_group_id = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }

            $updateValue[] = array(
                'website_id' => $tierPrice->website,
                'cust_group' => $tierPrice->customer_group_id,
                'price_qty'  => $tierPrice->qty,
                'price'      => $tierPrice->price
            );

        }

        return $updateValue;
    }
}
