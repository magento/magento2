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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart api for product
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Checkout_Model_Cart_Product_Api_V2 extends Mage_Checkout_Model_Cart_Product_Api
{

    /**
     * Return an Array of Object attributes.
     *
     * @param Mixed $data
     * @return Array
     */
    protected function _prepareProductsData($data){
        if (is_object($data)) {
            $arr = get_object_vars($data);
            foreach ($arr as $key => $value) {
                $assocArr = array();
                if (is_array($value)) {
                    foreach ($value as $v) {
                        if (is_object($v) && count(get_object_vars($v))==2
                            && isset($v->key) && isset($v->value)) {
                            $assocArr[$v->key] = $v->value;
                        }
                    }
                }
                if (!empty($assocArr)) {
                    $arr[$key] = $assocArr;
                }
            }
            $arr = $this->_prepareProductsData($arr);
            return parent::_prepareProductsData($arr);
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_object($value) || is_array($value)) {
                    $data[$key] = $this->_prepareProductsData($value);
                } else {
                    $data[$key] = $value;
                }
            }
            return parent::_prepareProductsData($data);
        }
        return $data;
    }
}
