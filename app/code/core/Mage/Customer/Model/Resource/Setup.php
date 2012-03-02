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
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customer resource setup model
 *
 * @category    Mage
 * @package     Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    /**
     * Prepare customer attribute values to save in additional table
     *
     * @param array $attr
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
            'is_visible'                => $this->_getValue($attr, 'visible', 1),
            'is_system'                 => $this->_getValue($attr, 'system', 1),
            'input_filter'              => $this->_getValue($attr, 'input_filter', null),
            'multiline_count'           => $this->_getValue($attr, 'multiline_count', 0),
            'validate_rules'            => $this->_getValue($attr, 'validate_rules', null),
            'data_model'                => $this->_getValue($attr, 'data', null),
            'sort_order'                => $this->_getValue($attr, 'position', 0)
        ));

        return $data;
    }

    /**
     * Add customer attributes to customer forms
     *
     * @return void
     */
    public function installCustomerForms()
    {
        $customer           = (int)$this->getEntityTypeId('customer');
        $customerAddress    = (int)$this->getEntityTypeId('customer_address');

        $attributeIds       = array();
        $select = $this->getConnection()->select()
            ->from(
                array('ea' => $this->getTable('eav_attribute')),
                array('entity_type_id', 'attribute_code', 'attribute_id'))
            ->where('ea.entity_type_id IN(?)', array($customer, $customerAddress));
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $attributeIds[$row['entity_type_id']][$row['attribute_code']] = $row['attribute_id'];
        }

        $data       = array();
        $entities   = $this->getDefaultEntities();
        $attributes = $entities['customer']['attributes'];
        foreach ($attributes as $attributeCode => $attribute) {
            $attributeId = $attributeIds[$customer][$attributeCode];
            $attribute['system'] = isset($attribute['system']) ? $attribute['system'] : true;
            $attribute['visible'] = isset($attribute['visible']) ? $attribute['visible'] : true;
            if ($attribute['system'] != true || $attribute['visible'] != false) {
                $usedInForms = array(
                    'customer_account_create',
                    'customer_account_edit',
                    'checkout_register',
                );
                if (!empty($attribute['adminhtml_only'])) {
                    $usedInForms = array('adminhtml_customer');
                } else {
                    $usedInForms[] = 'adminhtml_customer';
                }
                if (!empty($attribute['admin_checkout'])) {
                    $usedInForms[] = 'adminhtml_checkout';
                }
                foreach ($usedInForms as $formCode) {
                    $data[] = array(
                        'form_code'     => $formCode,
                        'attribute_id'  => $attributeId
                    );
                }
            }
        }

        $attributes = $entities['customer_address']['attributes'];
        foreach ($attributes as $attributeCode => $attribute) {
            $attributeId = $attributeIds[$customerAddress][$attributeCode];
            $attribute['system'] = isset($attribute['system']) ? $attribute['system'] : true;
            $attribute['visible'] = isset($attribute['visible']) ? $attribute['visible'] : true;
            if (false === ($attribute['system'] == true && $attribute['visible'] == false)) {
                $usedInForms = array(
                    'adminhtml_customer_address',
                    'customer_address_edit',
                    'customer_register_address'
                );
                foreach ($usedInForms as $formCode) {
                    $data[] = array(
                        'form_code'     => $formCode,
                        'attribute_id'  => $attributeId
                    );
                }
            }
        }

        if ($data) {
            $this->getConnection()->insertMultiple($this->getTable('customer_form_attribute'), $data);
        }
    }

    /**
     * Retreive default entities: customer, customer_address
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = array(
            'customer'                       => array(
                'entity_model'                   => 'Mage_Customer_Model_Resource_Customer',
                'attribute_model'                => 'Mage_Customer_Model_Attribute',
                'table'                          => 'customer_entity',
                'increment_model'                => 'Mage_Eav_Model_Entity_Increment_Numeric',
                'additional_attribute_table'     => 'customer_eav_attribute',
                'entity_attribute_collection'    => 'Mage_Customer_Model_Resource_Attribute_Collection',
                'attributes'                     => array(
                    'website_id'         => array(
                        'type'               => 'static',
                        'label'              => 'Associate to Website',
                        'input'              => 'select',
                        'source'             => 'Mage_Customer_Model_Customer_Attribute_Source_Website',
                        'backend'            => 'Mage_Customer_Model_Customer_Attribute_Backend_Website',
                        'sort_order'         => 10,
                        'position'           => 10,
                        'adminhtml_only'     => 1,
                    ),
                    'store_id'           => array(
                        'type'               => 'static',
                        'label'              => 'Create In',
                        'input'              => 'select',
                        'source'             => 'Mage_Customer_Model_Customer_Attribute_Source_Store',
                        'backend'            => 'Mage_Customer_Model_Customer_Attribute_Backend_Store',
                        'sort_order'         => 20,
                        'visible'            => false,
                        'adminhtml_only'     => 1,
                    ),
                    'created_in'         => array(
                        'type'               => 'varchar',
                        'label'              => 'Created From',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 20,
                        'position'           => 20,
                        'adminhtml_only'     => 1,
                    ),
                    'prefix'             => array(
                        'type'               => 'varchar',
                        'label'              => 'Prefix',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 30,
                        'visible'            => false,
                        'system'             => false,
                        'position'           => 30,
                    ),
                    'firstname'          => array(
                        'type'               => 'varchar',
                        'label'              => 'First Name',
                        'input'              => 'text',
                        'sort_order'         => 40,
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position'           => 40,
                    ),
                    'middlename'         => array(
                        'type'               => 'varchar',
                        'label'              => 'Middle Name/Initial',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 50,
                        'visible'            => false,
                        'system'             => false,
                        'position'           => 50,
                    ),
                    'lastname'           => array(
                        'type'               => 'varchar',
                        'label'              => 'Last Name',
                        'input'              => 'text',
                        'sort_order'         => 60,
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position'           => 60,
                    ),
                    'suffix'             => array(
                        'type'               => 'varchar',
                        'label'              => 'Suffix',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 70,
                        'visible'            => false,
                        'system'             => false,
                        'position'           => 70,
                    ),
                    'email'              => array(
                        'type'               => 'static',
                        'label'              => 'Email',
                        'input'              => 'text',
                        'sort_order'         => 80,
                        'validate_rules'     => 'a:1:{s:16:"input_validation";s:5:"email";}',
                        'position'           => 80,
                        'admin_checkout'    => 1
                    ),
                    'group_id'           => array(
                        'type'               => 'static',
                        'label'              => 'Group',
                        'input'              => 'select',
                        'source'             => 'Mage_Customer_Model_Customer_Attribute_Source_Group',
                        'sort_order'         => 25,
                        'position'           => 25,
                        'adminhtml_only'     => 1,
                        'admin_checkout'     => 1,
                    ),
                    'dob'                => array(
                        'type'               => 'datetime',
                        'label'              => 'Date Of Birth',
                        'input'              => 'date',
                        'frontend'           => 'Mage_Eav_Model_Entity_Attribute_Frontend_Datetime',
                        'backend'            => 'Mage_Eav_Model_Entity_Attribute_Backend_Datetime',
                        'required'           => false,
                        'sort_order'         => 90,
                        'visible'            => false,
                        'system'             => false,
                        'input_filter'       => 'date',
                        'validate_rules'     => 'a:1:{s:16:"input_validation";s:4:"date";}',
                        'position'           => 90,
                        'admin_checkout'     => 1,
                    ),
                    'password_hash'      => array(
                        'type'               => 'varchar',
                        'input'              => 'hidden',
                        'backend'            => 'Mage_Customer_Model_Customer_Attribute_Backend_Password',
                        'required'           => false,
                        'sort_order'         => 81,
                        'visible'            => false,
                    ),
                    'default_billing'    => array(
                        'type'               => 'int',
                        'label'              => 'Default Billing Address',
                        'input'              => 'text',
                        'backend'            => 'Mage_Customer_Model_Customer_Attribute_Backend_Billing',
                        'required'           => false,
                        'sort_order'         => 82,
                        'visible'            => false,
                    ),
                    'default_shipping'   => array(
                        'type'               => 'int',
                        'label'              => 'Default Shipping Address',
                        'input'              => 'text',
                        'backend'            => 'Mage_Customer_Model_Customer_Attribute_Backend_Shipping',
                        'required'           => false,
                        'sort_order'         => 83,
                        'visible'            => false,
                    ),
                    'taxvat'             => array(
                        'type'               => 'varchar',
                        'label'              => 'Tax/VAT Number',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 100,
                        'visible'            => false,
                        'system'             => false,
                        'validate_rules'     => 'a:1:{s:15:"max_text_length";i:255;}',
                        'position'           => 100,
                        'admin_checkout'     => 1,
                    ),
                    'confirmation'       => array(
                        'type'               => 'varchar',
                        'label'              => 'Is Confirmed',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 85,
                        'visible'            => false,
                    ),
                    'created_at'         => array(
                        'type'               => 'static',
                        'label'              => 'Created At',
                        'input'              => 'date',
                        'required'           => false,
                        'sort_order'         => 86,
                        'visible'            => false,
                        'system'             => false,
                    ),
                    'gender'             => array(
                        'type'               => 'int',
                        'label'              => 'Gender',
                        'input'              => 'select',
                        'source'             => 'Mage_Eav_Model_Entity_Attribute_Source_Table',
                        'required'           => false,
                        'sort_order'         => 110,
                        'visible'            => false,
                        'system'             => false,
                        'validate_rules'     => 'a:0:{}',
                        'position'           => 110,
                        'admin_checkout'     => 1,
                        'option'             => array('values' => array('Male', 'Female'))
                    ),
                )
            ),

            'customer_address'               => array(
                'entity_model'                   => 'Mage_Customer_Model_Resource_Address',
                'attribute_model'                => 'Mage_Customer_Model_Attribute',
                'table'                          => 'customer_address_entity',
                'additional_attribute_table'     => 'customer_eav_attribute',
                'entity_attribute_collection'    => 'Mage_Customer_Model_Resource_Address_Attribute_Collection',
                'attributes'                     => array(
                    'prefix'             => array(
                        'type'               => 'varchar',
                        'label'              => 'Prefix',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 10,
                        'visible'            => false,
                        'system'             => false,
                        'position'           => 10,
                    ),
                    'firstname'          => array(
                        'type'               => 'varchar',
                        'label'              => 'First Name',
                        'input'              => 'text',
                        'sort_order'         => 20,
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position'           => 20,
                    ),
                    'middlename'         => array(
                        'type'               => 'varchar',
                        'label'              => 'Middle Name/Initial',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 30,
                        'visible'            => false,
                        'system'             => false,
                        'position'           => 30,
                    ),
                    'lastname'           => array(
                        'type'               => 'varchar',
                        'label'              => 'Last Name',
                        'input'              => 'text',
                        'sort_order'         => 40,
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position'           => 40,
                    ),
                    'suffix'             => array(
                        'type'               => 'varchar',
                        'label'              => 'Suffix',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 50,
                        'visible'            => false,
                        'system'             => false,
                        'position'           => 50,
                    ),
                    'company'            => array(
                        'type'               => 'varchar',
                        'label'              => 'Company',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 60,
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position'           => 60,
                    ),
                    'street'             => array(
                        'type'               => 'text',
                        'label'              => 'Street Address',
                        'input'              => 'multiline',
                        'backend'            => 'Mage_Customer_Model_Resource_Address_Attribute_Backend_Street',
                        'sort_order'         => 70,
                        'multiline_count'    => 2,
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position'           => 70,
                    ),
                    'city'               => array(
                        'type'               => 'varchar',
                        'label'              => 'City',
                        'input'              => 'text',
                        'sort_order'         => 80,
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position'           => 80,
                    ),
                    'country_id'         => array(
                        'type'               => 'varchar',
                        'label'              => 'Country',
                        'input'              => 'select',
                        'source'             => 'Mage_Customer_Model_Resource_Address_Attribute_Source_Country',
                        'sort_order'         => 90,
                        'position'           => 90,
                    ),
                    'region'             => array(
                        'type'               => 'varchar',
                        'label'              => 'State/Province',
                        'input'              => 'text',
                        'backend'            => 'Mage_Customer_Model_Resource_Address_Attribute_Backend_Region',
                        'required'           => false,
                        'sort_order'         => 100,
                        'position'           => 100,
                    ),
                    'region_id'          => array(
                        'type'               => 'int',
                        'label'              => 'State/Province',
                        'input'              => 'hidden',
                        'source'             => 'Mage_Customer_Model_Resource_Address_Attribute_Source_Region',
                        'required'           => false,
                        'sort_order'         => 100,
                        'position'           => 100,
                    ),
                    'postcode'           => array(
                        'type'               => 'varchar',
                        'label'              => 'Zip/Postal Code',
                        'input'              => 'text',
                        'sort_order'         => 110,
                        'validate_rules'     => 'a:0:{}',
                        'data'               => 'Mage_Customer_Model_Attribute_Data_Postcode',
                        'position'           => 110,
                    ),
                    'telephone'          => array(
                        'type'               => 'varchar',
                        'label'              => 'Telephone',
                        'input'              => 'text',
                        'sort_order'         => 120,
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position'           => 120,
                    ),
                    'fax'                => array(
                        'type'               => 'varchar',
                        'label'              => 'Fax',
                        'input'              => 'text',
                        'required'           => false,
                        'sort_order'         => 130,
                        'validate_rules'     => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position'           => 130,
                    ),
                )
            )
        );
        return $entities;
    }
}
