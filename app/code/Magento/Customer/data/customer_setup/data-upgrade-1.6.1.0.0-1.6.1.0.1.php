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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer \Magento\Customer\Model\Resource\Setup */
$installer = $this;

$disableAGCAttributeCode = 'disable_auto_group_change';

$installer->addAttribute(
    'customer',
    $disableAGCAttributeCode,
    array(
        'type' => 'static',
        'label' => 'Disable Automatic Group Change Based on VAT ID',
        'input' => 'boolean',
        'backend' => 'Magento\Customer\Model\Attribute\Backend\Data\Boolean',
        'position' => 28,
        'required' => false
    )
);

$attributesInfo = array(
    'vat_id' => array(
        'label' => 'VAT number',
        'type' => 'varchar',
        'input' => 'text',
        'position' => 140,
        'visible' => true,
        'required' => false
    ),
    'vat_is_valid' => array(
        'label' => 'VAT number validity',
        'visible' => false,
        'required' => false,
        'type' => 'int'
    ),
    'vat_request_id' => array(
        'label' => 'VAT number validation request ID',
        'type' => 'varchar',
        'visible' => false,
        'required' => false
    ),
    'vat_request_date' => array(
        'label' => 'VAT number validation request date',
        'type' => 'varchar',
        'visible' => false,
        'required' => false
    ),
    'vat_request_success' => array(
        'label' => 'VAT number validation request success',
        'visible' => false,
        'required' => false,
        'type' => 'int'
    )
);

foreach ($attributesInfo as $attributeCode => $attributeParams) {
    $installer->addAttribute('customer_address', $attributeCode, $attributeParams);
}
