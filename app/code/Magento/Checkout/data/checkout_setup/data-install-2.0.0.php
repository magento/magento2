<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Checkout\Model\Resource\Setup */
$installer = $this;

$installer->startSetup();

$setup = $installer->getConnection();

$select = $setup->select()->from(
    $installer->getTable('core_config_data'),
    'COUNT(*)'
)->where(
    'path=?',
    'customer/address/prefix_show'
)->where(
    'value NOT LIKE ?',
    '0'
);
$showPrefix = (bool)$installer->getCustomerAddress()->getConfig('prefix_show') || $setup->fetchOne($select) > 0;

$select = $setup->select()->from(
    $installer->getTable('core_config_data'),
    'COUNT(*)'
)->where(
    'path=?',
    'customer/address/middlename_show'
)->where(
    'value NOT LIKE ?',
    '0'
);
$showMiddlename = (bool)$installer->getCustomerAddress()->getConfig(
    'middlename_show'
) || $setup->fetchOne(
    $select
) > 0;

$select = $setup->select()->from(
    $installer->getTable('core_config_data'),
    'COUNT(*)'
)->where(
    'path=?',
    'customer/address/suffix_show'
)->where(
    'value NOT LIKE ?',
    '0'
);
$showSuffix = (bool)$installer->getCustomerAddress()->getConfig('suffix_show') || $setup->fetchOne($select) > 0;

$select = $setup->select()->from(
    $installer->getTable('core_config_data'),
    'COUNT(*)'
)->where(
    'path=?',
    'customer/address/dob_show'
)->where(
    'value NOT LIKE ?',
    '0'
);
$showDob = (bool)$installer->getCustomerAddress()->getConfig('dob_show') || $setup->fetchOne($select) > 0;

$select = $setup->select()->from(
    $installer->getTable('core_config_data'),
    'COUNT(*)'
)->where(
    'path=?',
    'customer/address/taxvat_show'
)->where(
    'value NOT LIKE ?',
    '0'
);
$showTaxVat = (bool)$installer->getCustomerAddress()->getConfig('taxvat_show') || $setup->fetchOne($select) > 0;

$customerEntityTypeId = $installer->getEntityTypeId('customer');
$addressEntityTypeId = $installer->getEntityTypeId('customer_address');

/**
 *****************************************************************************
 * checkout/onepage/register
 *****************************************************************************
 */

$setup->insert(
    $installer->getTable('eav_form_type'),
    [
        'code' => 'checkout_onepage_register',
        'label' => 'checkout_onepage_register',
        'is_system' => 1,
        'theme' => '',
        'store_id' => 0
    ]
);
$formTypeId = $setup->lastInsertId($installer->getTable('eav_form_type'));

$setup->insert(
    $installer->getTable('eav_form_type_entity'),
    ['type_id' => $formTypeId, 'entity_type_id' => $customerEntityTypeId]
);
$setup->insert(
    $installer->getTable('eav_form_type_entity'),
    ['type_id' => $formTypeId, 'entity_type_id' => $addressEntityTypeId]
);

$elementSort = 0;
if ($showPrefix) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'prefix'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'firstname'),
        'sort_order' => $elementSort++
    ]
);
if ($showMiddlename) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'middlename'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'lastname'),
        'sort_order' => $elementSort++
    ]
);
if ($showSuffix) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'suffix'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'company'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($customerEntityTypeId, 'email'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'street'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'city'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'region'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'postcode'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'country_id'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'telephone'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'fax'),
        'sort_order' => $elementSort++
    ]
);
if ($showDob) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($customerEntityTypeId, 'dob'),
            'sort_order' => $elementSort++
        ]
    );
}
if ($showTaxVat) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($customerEntityTypeId, 'taxvat'),
            'sort_order' => $elementSort++
        ]
    );
}

/**
 *****************************************************************************
 * checkout/onepage/register_guest
 *****************************************************************************
 */

$setup->insert(
    $installer->getTable('eav_form_type'),
    [
        'code' => 'checkout_onepage_register_guest',
        'label' => 'checkout_onepage_register_guest',
        'is_system' => 1,
        'theme' => '',
        'store_id' => 0
    ]
);
$formTypeId = $setup->lastInsertId($installer->getTable('eav_form_type'));

$setup->insert(
    $installer->getTable('eav_form_type_entity'),
    ['type_id' => $formTypeId, 'entity_type_id' => $customerEntityTypeId]
);
$setup->insert(
    $installer->getTable('eav_form_type_entity'),
    ['type_id' => $formTypeId, 'entity_type_id' => $addressEntityTypeId]
);

$elementSort = 0;
if ($showPrefix) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'prefix'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'firstname'),
        'sort_order' => $elementSort++
    ]
);
if ($showMiddlename) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'middlename'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'lastname'),
        'sort_order' => $elementSort++
    ]
);
if ($showSuffix) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'suffix'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'company'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($customerEntityTypeId, 'email'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'street'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'city'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'region'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'postcode'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'country_id'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'telephone'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'fax'),
        'sort_order' => $elementSort++
    ]
);
if ($showDob) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($customerEntityTypeId, 'dob'),
            'sort_order' => $elementSort++
        ]
    );
}
if ($showTaxVat) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($customerEntityTypeId, 'taxvat'),
            'sort_order' => $elementSort++
        ]
    );
}

/**
 *****************************************************************************
 * checkout/onepage/billing_address
 *****************************************************************************
 */

$setup->insert(
    $installer->getTable('eav_form_type'),
    [
        'code' => 'checkout_onepage_billing_address',
        'label' => 'checkout_onepage_billing_address',
        'is_system' => 1,
        'theme' => '',
        'store_id' => 0
    ]
);
$formTypeId = $setup->lastInsertId($installer->getTable('eav_form_type'));

$setup->insert(
    $installer->getTable('eav_form_type_entity'),
    ['type_id' => $formTypeId, 'entity_type_id' => $addressEntityTypeId]
);

$elementSort = 0;
if ($showPrefix) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'prefix'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'firstname'),
        'sort_order' => $elementSort++
    ]
);
if ($showMiddlename) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'middlename'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'lastname'),
        'sort_order' => $elementSort++
    ]
);
if ($showSuffix) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'suffix'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'company'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'street'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'city'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'region'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'postcode'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'country_id'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'telephone'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'fax'),
        'sort_order' => $elementSort++
    ]
);

/**
 *****************************************************************************
 * checkout/onepage/shipping_address
 *****************************************************************************
 */

$setup->insert(
    $installer->getTable('eav_form_type'),
    [
        'code' => 'checkout_onepage_shipping_address',
        'label' => 'checkout_onepage_shipping_address',
        'is_system' => 1,
        'theme' => '',
        'store_id' => 0
    ]
);
$formTypeId = $setup->lastInsertId($installer->getTable('eav_form_type'));

$setup->insert(
    $installer->getTable('eav_form_type_entity'),
    ['type_id' => $formTypeId, 'entity_type_id' => $addressEntityTypeId]
);

$elementSort = 0;
if ($showPrefix) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'prefix'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'firstname'),
        'sort_order' => $elementSort++
    ]
);
if ($showMiddlename) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'middlename'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'lastname'),
        'sort_order' => $elementSort++
    ]
);
if ($showSuffix) {
    $setup->insert(
        $installer->getTable('eav_form_element'),
        [
            'type_id' => $formTypeId,
            'fieldset_id' => null,
            'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'suffix'),
            'sort_order' => $elementSort++
        ]
    );
}
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'company'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'street'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'city'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'region'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'postcode'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'country_id'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'telephone'),
        'sort_order' => $elementSort++
    ]
);
$setup->insert(
    $installer->getTable('eav_form_element'),
    [
        'type_id' => $formTypeId,
        'fieldset_id' => null,
        'attribute_id' => $installer->getAttributeId($addressEntityTypeId, 'fax'),
        'sort_order' => $elementSort++
    ]
);

$table = $installer->getTable('core_config_data');

$select = $setup->select()->from(
    $table,
    ['config_id', 'value']
)->where(
    'path = ?',
    'checkout/options/onepage_checkout_disabled'
);

$data = $setup->fetchAll($select);

if ($data) {
    try {
        $setup->beginTransaction();

        foreach ($data as $value) {
            $bind = ['path' => 'checkout/options/onepage_checkout_enabled', 'value' => !(bool)$value['value']];
            $where = 'config_id = ' . $value['config_id'];
            $setup->update($table, $bind, $where);
        }

        $setup->commit();
    } catch (\Exception $e) {
        $setup->rollback();
        throw $e;
    }
}

$installer->endSetup();
