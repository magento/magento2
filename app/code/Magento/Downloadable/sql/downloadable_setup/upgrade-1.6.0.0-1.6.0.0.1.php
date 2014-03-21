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
 * @category    Magento
 * @package     Magento_Downloadable
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;

$msrpEnabled = $installer->getAttribute('catalog_product', 'msrp_enabled', 'apply_to');
if ($msrpEnabled && strstr($msrpEnabled, \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) == false) {
    $installer->updateAttribute(
        'catalog_product',
        'msrp_enabled',
        array('apply_to' => $msrpEnabled . ',' . \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE)
    );
}

$msrpDisplay = $installer->getAttribute('catalog_product', 'msrp_display_actual_price_type', 'apply_to');
if ($msrpDisplay && strstr($msrpEnabled, \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) == false) {
    $installer->updateAttribute(
        'catalog_product',
        'msrp_display_actual_price_type',
        array('apply_to' => $msrpDisplay . ',' . \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE)
    );
}

$msrp = $installer->getAttribute('catalog_product', 'msrp', 'apply_to');
if ($msrp && strstr($msrpEnabled, \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) == false) {
    $installer->updateAttribute(
        'catalog_product',
        'msrp',
        array('apply_to' => $msrp . ',' . \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE)
    );
}
