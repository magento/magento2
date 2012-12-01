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
 * Source model for 'msrp_enabled' product attribute
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Attribute_Source_Msrp_Type_Enabled
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Enable MAP
     */
    const MSRP_ENABLE_YES = 1;

    /**
     * Disable MAP
     */
    const MSRP_ENABLE_NO = 0;

    /**
     * Get value from the store configuration settings
     */
    const MSRP_ENABLE_USE_CONFIG = 2;

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Yes'),
                    'value' => self::MSRP_ENABLE_YES
                ),
                array(
                    'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('No'),
                    'value' => self::MSRP_ENABLE_NO
                ),
                array(
                    'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use config'),
                    'value' => self::MSRP_ENABLE_USE_CONFIG
                )
            );
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array(
            'unsigned'  => false,
            'default'   => null,
            'extra'     => null
        );

        if (Mage::helper('Mage_Core_Helper_Data')->useDbCompatibleMode()) {
            $column['type']     = 'tinyint(1)';
            $column['is_null']  = true;
        } else {
            $column['type']     = Varien_Db_Ddl_Table::TYPE_SMALLINT;
            $column['length']   = 1;
            $column['nullable'] = true;
            $column['comment']  = $attributeCode . ' column';
        }

        return array($attributeCode => $column);
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
