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
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$msrpEnabled = $installer->getAttribute('catalog_product', 'msrp_enabled', 'apply_to');
if ($msrpEnabled && strstr($msrpEnabled, Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) == false) {
    $installer->updateAttribute('catalog_product', 'msrp_enabled', array(
        'apply_to'      => $msrpEnabled . ',' . Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
    ));
}

$msrpDisplay = $installer->getAttribute('catalog_product', 'msrp_display_actual_price_type', 'apply_to');
if ($msrpDisplay && strstr($msrpEnabled, Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) == false) {
    $installer->updateAttribute('catalog_product', 'msrp_display_actual_price_type', array(
        'apply_to'      => $msrpDisplay . ',' . Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
    ));
}

$msrp = $installer->getAttribute('catalog_product', 'msrp', 'apply_to');
if ($msrp && strstr($msrpEnabled, Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) == false) {
    $installer->updateAttribute('catalog_product', 'msrp', array(
        'apply_to'      => $msrp . ',' . Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
    ));
}
