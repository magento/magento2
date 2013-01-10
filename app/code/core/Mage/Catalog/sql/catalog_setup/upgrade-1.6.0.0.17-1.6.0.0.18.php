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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $this Mage_Catalog_Model_Resource_Setup */
$this->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'quantity_and_stock_status', array(
    'group'             => 'General',
    'type'              => 'int',
    'backend'           => 'Mage_Catalog_Model_Product_Attribute_Backend_Stock',
    'frontend'          => '',
    'label'             => 'Quantity',
    'input'             => 'select',
    'class'             => '',
    'input_renderer'    => 'Mage_CatalogInventory_Block_Adminhtml_Form_Field_Stock',
    'source'            => 'Mage_CatalogInventory_Model_Stock_Status',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'default'           => Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK,
    'user_defined'      => false,
    'visible'           => true,
    'required'          => false,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'unique'            => false,
    'is_configurable'   => false,
));
