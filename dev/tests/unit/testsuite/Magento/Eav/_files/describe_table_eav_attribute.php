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

return array(
    'attribute_id' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'attribute_id',
        'COLUMN_POSITION' => 1,
        'DATA_TYPE' => 'smallint',
        'DEFAULT' => null,
        'NULLABLE' => false,
        'LENGTH' => null,
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => true,
        'PRIMARY' => true,
        'PRIMARY_POSITION' => 1,
        'IDENTITY' => true
    ),
    'entity_type_id' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'entity_type_id',
        'COLUMN_POSITION' => 2,
        'DATA_TYPE' => 'smallint',
        'DEFAULT' => '0',
        'NULLABLE' => false,
        'LENGTH' => null,
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => true,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'attribute_code' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'attribute_code',
        'COLUMN_POSITION' => 3,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '255',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'attribute_model' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'attribute_model',
        'COLUMN_POSITION' => 4,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '255',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'backend_model' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'backend_model',
        'COLUMN_POSITION' => 5,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '255',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'backend_type' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'backend_type',
        'COLUMN_POSITION' => 6,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => 'static',
        'NULLABLE' => false,
        'LENGTH' => '8',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'backend_table' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'backend_table',
        'COLUMN_POSITION' => 7,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '255',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'frontend_model' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'frontend_model',
        'COLUMN_POSITION' => 8,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '255',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'frontend_input' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'frontend_input',
        'COLUMN_POSITION' => 9,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '50',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'frontend_label' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'frontend_label',
        'COLUMN_POSITION' => 10,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '255',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'frontend_class' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'frontend_class',
        'COLUMN_POSITION' => 11,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '255',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'source_model' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'source_model',
        'COLUMN_POSITION' => 12,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '255',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'is_required' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'is_required',
        'COLUMN_POSITION' => 13,
        'DATA_TYPE' => 'smallint',
        'DEFAULT' => '0',
        'NULLABLE' => false,
        'LENGTH' => null,
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => true,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'is_user_defined' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'is_user_defined',
        'COLUMN_POSITION' => 14,
        'DATA_TYPE' => 'smallint',
        'DEFAULT' => '0',
        'NULLABLE' => false,
        'LENGTH' => null,
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => true,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'default_value' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'default_value',
        'COLUMN_POSITION' => 15,
        'DATA_TYPE' => 'text',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => null,
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'is_unique' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'is_unique',
        'COLUMN_POSITION' => 16,
        'DATA_TYPE' => 'smallint',
        'DEFAULT' => '0',
        'NULLABLE' => false,
        'LENGTH' => null,
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => true,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    ),
    'note' => array(
        'SCHEMA_NAME' => null,
        'TABLE_NAME' => 'eav_attribute',
        'COLUMN_NAME' => 'note',
        'COLUMN_POSITION' => 17,
        'DATA_TYPE' => 'varchar',
        'DEFAULT' => null,
        'NULLABLE' => true,
        'LENGTH' => '255',
        'SCALE' => null,
        'PRECISION' => null,
        'UNSIGNED' => null,
        'PRIMARY' => false,
        'PRIMARY_POSITION' => null,
        'IDENTITY' => false
    )
);
