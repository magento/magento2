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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Watermark position config source model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Config_Source_Watermark_Position implements Mage_Core_Model_Option_ArrayInterface
{

    /**
     * Get available options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'stretch',         'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Stretch')),
            array('value' => 'tile',            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Tile')),
            array('value' => 'top-left',        'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Top/Left')),
            array('value' => 'top-right',       'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Top/Right')),
            array('value' => 'bottom-left',     'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Bottom/Left')),
            array('value' => 'bottom-right',    'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Bottom/Right')),
            array('value' => 'center',          'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Center')),
        );
    }

}
