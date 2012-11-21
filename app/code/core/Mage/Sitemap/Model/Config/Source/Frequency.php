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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sitemap_Model_Config_Source_Frequency implements Mage_Core_Model_Option_ArrayInterface
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'always', 'label'=>Mage::helper('Mage_Sitemap_Helper_Data')->__('Always')),
            array('value'=>'hourly', 'label'=>Mage::helper('Mage_Sitemap_Helper_Data')->__('Hourly')),
            array('value'=>'daily', 'label'=>Mage::helper('Mage_Sitemap_Helper_Data')->__('Daily')),
            array('value'=>'weekly', 'label'=>Mage::helper('Mage_Sitemap_Helper_Data')->__('Weekly')),
            array('value'=>'monthly', 'label'=>Mage::helper('Mage_Sitemap_Helper_Data')->__('Monthly')),
            array('value'=>'yearly', 'label'=>Mage::helper('Mage_Sitemap_Helper_Data')->__('Yearly')),
            array('value'=>'never', 'label'=>Mage::helper('Mage_Sitemap_Helper_Data')->__('Never')),
        );
    }
}
