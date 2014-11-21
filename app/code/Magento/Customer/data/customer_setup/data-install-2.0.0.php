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

/* @var $this \Magento\Customer\Model\Resource\Setup */
$installer = $this;

$installer->startSetup();

// insert default customer groups
$installer->getConnection()->insertForce(
    $installer->getTable('customer_group'),
    array('customer_group_id' => 0, 'customer_group_code' => 'NOT LOGGED IN', 'tax_class_id' => 3)
);
$installer->getConnection()->insertForce(
    $installer->getTable('customer_group'),
    array('customer_group_id' => 1, 'customer_group_code' => 'General', 'tax_class_id' => 3)
);
$installer->getConnection()->insertForce(
    $installer->getTable('customer_group'),
    array('customer_group_id' => 2, 'customer_group_code' => 'Wholesale', 'tax_class_id' => 3)
);
$installer->getConnection()->insertForce(
    $installer->getTable('customer_group'),
    array('customer_group_id' => 3, 'customer_group_code' => 'Retailer', 'tax_class_id' => 3)
);

$installer->installEntities();

$installer->installCustomerForms();

// Add reset password link token attribute
$installer->addAttribute(
    'customer',
    'rp_token',
    array('type' => 'varchar', 'input' => 'hidden', 'visible' => false, 'required' => false)
);

// Add reset password link token creation date attribute
$installer->addAttribute(
    'customer',
    'rp_token_created_at',
    array(
        'type' => 'datetime',
        'input' => 'date',
        'validate_rules' => 'a:1:{s:16:"input_validation";s:4:"date";}',
        'visible' => false,
        'required' => false
    )
);

// Add VAT attributes to customer address
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

$disableAGCAttribute = $installer->getEavConfig()->getAttribute('customer', $disableAGCAttributeCode);
$disableAGCAttribute->setData('used_in_forms', array('adminhtml_customer'));
$disableAGCAttribute->save();

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

$vatIdAttribute = $installer->getEavConfig()->getAttribute('customer_address', 'vat_id');
$vatIdAttribute->setData(
    'used_in_forms',
    array('adminhtml_customer_address', 'customer_address_edit', 'customer_register_address')
);
$vatIdAttribute->save();

$entities = $installer->getDefaultEntities();
foreach ($entities as $entityName => $entity) {
    $installer->addEntityType($entityName, $entity);
}

$installer->updateAttribute(
    'customer_address',
    'street',
    'backend_model',
    'Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend'
);

$installer = $this->createMigrationSetup();

$installer->appendClassAliasReplace(
    'customer_eav_attribute',
    'data_model',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    array('attribute_id')
);
$installer->doUpdateClassAliases();

$installer->endSetup();
