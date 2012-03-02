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
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Google Optimizer Source Model
 *
 * @category    Mage
 * @package     Mage_GoogleOptimizer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleOptimizer_Model_Adminhtml_System_Config_Source_Googleoptimizer_Conversionpages
{
    
    public function toOptionArray()
    {
        return array(
            array('value' => '',                                'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('-- Please Select --')),
            array('value' => 'other',                           'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Other')),
            array('value' => 'checkout_cart',                   'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Shopping Cart')),
            array('value' => 'checkout_onepage',                'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('One Page Checkout')),
            array('value' => 'checkout_multishipping',          'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Multi Address Checkout')),
            array('value' => 'checkout_onepage_success',        'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Order Success (One Page Checkout)')),
            array('value' => 'checkout_multishipping_success',  'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Order Success (Multi Address Checkout)')),
            array('value' => 'customer_account_create',         'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Account Registration')),
        );
    }
    
}
