<?php
/**
 * Customer resource setup model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource;

class Setup extends \Magento\Eav\Model\Entity\Setup
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @param \Magento\Eav\Model\Entity\Setup\Context $context
     * @param string $resourceName
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $attrGroupCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Setup\Context $context,
        $resourceName,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $attrGroupCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        $moduleName = 'Magento_Customer',
        $connectionName = \Magento\Framework\Module\Updater\SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct(
            $context,
            $resourceName,
            $cache,
            $attrGroupCollectionFactory,
            $moduleName,
            $connectionName
        );
    }

    /**
     * Add customer attributes to customer forms
     *
     * @return void
     */
    public function installCustomerForms()
    {
        $customer = (int)$this->getEntityTypeId('customer');
        $customerAddress = (int)$this->getEntityTypeId('customer_address');

        $attributeIds = [];
        $select = $this->getConnection()->select()->from(
            ['ea' => $this->getTable('eav_attribute')],
            ['entity_type_id', 'attribute_code', 'attribute_id']
        )->where(
            'ea.entity_type_id IN(?)',
            [$customer, $customerAddress]
        );
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $attributeIds[$row['entity_type_id']][$row['attribute_code']] = $row['attribute_id'];
        }

        $data = [];
        $entities = $this->getDefaultEntities();
        $attributes = $entities['customer']['attributes'];
        foreach ($attributes as $attributeCode => $attribute) {
            $attributeId = $attributeIds[$customer][$attributeCode];
            $attribute['system'] = isset($attribute['system']) ? $attribute['system'] : true;
            $attribute['visible'] = isset($attribute['visible']) ? $attribute['visible'] : true;
            if ($attribute['system'] != true || $attribute['visible'] != false) {
                $usedInForms = ['customer_account_create', 'customer_account_edit', 'checkout_register'];
                if (!empty($attribute['adminhtml_only'])) {
                    $usedInForms = ['adminhtml_customer'];
                } else {
                    $usedInForms[] = 'adminhtml_customer';
                }
                if (!empty($attribute['admin_checkout'])) {
                    $usedInForms[] = 'adminhtml_checkout';
                }
                foreach ($usedInForms as $formCode) {
                    $data[] = ['form_code' => $formCode, 'attribute_id' => $attributeId];
                }
            }
        }

        $attributes = $entities['customer_address']['attributes'];
        foreach ($attributes as $attributeCode => $attribute) {
            $attributeId = $attributeIds[$customerAddress][$attributeCode];
            $attribute['system'] = isset($attribute['system']) ? $attribute['system'] : true;
            $attribute['visible'] = isset($attribute['visible']) ? $attribute['visible'] : true;
            if (false === ($attribute['system'] == true && $attribute['visible'] == false)) {
                $usedInForms = [
                    'adminhtml_customer_address',
                    'customer_address_edit',
                    'customer_register_address',
                ];
                foreach ($usedInForms as $formCode) {
                    $data[] = ['form_code' => $formCode, 'attribute_id' => $attributeId];
                }
            }
        }

        if ($data) {
            $this->getConnection()->insertMultiple($this->getTable('customer_form_attribute'), $data);
        }
    }

    /**
     * Retrieve default entities: customer, customer_address
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = [
            'customer' => [
                'entity_model' => 'Magento\Customer\Model\Resource\Customer',
                'attribute_model' => 'Magento\Customer\Model\Attribute',
                'table' => 'customer_entity',
                'increment_model' => 'Magento\Eav\Model\Entity\Increment\Numeric',
                'additional_attribute_table' => 'customer_eav_attribute',
                'entity_attribute_collection' => 'Magento\Customer\Model\Resource\Attribute\Collection',
                'attributes' => [
                    'website_id' => [
                        'type' => 'static',
                        'label' => 'Associate to Website',
                        'input' => 'select',
                        'source' => 'Magento\Customer\Model\Customer\Attribute\Source\Website',
                        'backend' => 'Magento\Customer\Model\Customer\Attribute\Backend\Website',
                        'sort_order' => 10,
                        'position' => 10,
                        'adminhtml_only' => 1,
                    ],
                    'store_id' => [
                        'type' => 'static',
                        'label' => 'Create In',
                        'input' => 'select',
                        'source' => 'Magento\Customer\Model\Customer\Attribute\Source\Store',
                        'backend' => 'Magento\Customer\Model\Customer\Attribute\Backend\Store',
                        'sort_order' => 20,
                        'visible' => false,
                        'adminhtml_only' => 1,
                    ],
                    'created_in' => [
                        'type' => 'varchar',
                        'label' => 'Created From',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 20,
                        'position' => 20,
                        'adminhtml_only' => 1,
                    ],
                    'prefix' => [
                        'type' => 'varchar',
                        'label' => 'Prefix',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 30,
                        'visible' => false,
                        'system' => false,
                        'position' => 30,
                    ],
                    'firstname' => [
                        'type' => 'varchar',
                        'label' => 'First Name',
                        'input' => 'text',
                        'sort_order' => 40,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 40,
                    ],
                    'middlename' => [
                        'type' => 'varchar',
                        'label' => 'Middle Name/Initial',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'visible' => false,
                        'system' => false,
                        'position' => 50,
                    ],
                    'lastname' => [
                        'type' => 'varchar',
                        'label' => 'Last Name',
                        'input' => 'text',
                        'sort_order' => 60,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 60,
                    ],
                    'suffix' => [
                        'type' => 'varchar',
                        'label' => 'Suffix',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 70,
                        'visible' => false,
                        'system' => false,
                        'position' => 70,
                    ],
                    'email' => [
                        'type' => 'static',
                        'label' => 'Email',
                        'input' => 'text',
                        'sort_order' => 80,
                        'validate_rules' => 'a:1:{s:16:"input_validation";s:5:"email";}',
                        'position' => 80,
                        'admin_checkout' => 1,
                    ],
                    'group_id' => [
                        'type' => 'static',
                        'label' => 'Group',
                        'input' => 'select',
                        'source' => 'Magento\Customer\Model\Customer\Attribute\Source\Group',
                        'sort_order' => 25,
                        'position' => 25,
                        'adminhtml_only' => 1,
                        'admin_checkout' => 1,
                    ],
                    'dob' => [
                        'type' => 'datetime',
                        'label' => 'Date Of Birth',
                        'input' => 'date',
                        'frontend' => 'Magento\Eav\Model\Entity\Attribute\Frontend\Datetime',
                        'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
                        'required' => false,
                        'sort_order' => 90,
                        'visible' => false,
                        'system' => false,
                        'input_filter' => 'date',
                        'validate_rules' => 'a:1:{s:16:"input_validation";s:4:"date";}',
                        'position' => 90,
                        'admin_checkout' => 1,
                    ],
                    'password_hash' => [
                        'type' => 'varchar',
                        'input' => 'hidden',
                        'backend' => 'Magento\Customer\Model\Customer\Attribute\Backend\Password',
                        'required' => false,
                        'sort_order' => 81,
                        'visible' => false,
                    ],
                    'default_billing' => [
                        'type' => 'int',
                        'label' => 'Default Billing Address',
                        'input' => 'text',
                        'backend' => 'Magento\Customer\Model\Customer\Attribute\Backend\Billing',
                        'required' => false,
                        'sort_order' => 82,
                        'visible' => false,
                    ],
                    'default_shipping' => [
                        'type' => 'int',
                        'label' => 'Default Shipping Address',
                        'input' => 'text',
                        'backend' => 'Magento\Customer\Model\Customer\Attribute\Backend\Shipping',
                        'required' => false,
                        'sort_order' => 83,
                        'visible' => false,
                    ],
                    'taxvat' => [
                        'type' => 'varchar',
                        'label' => 'Tax/VAT Number',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 100,
                        'visible' => false,
                        'system' => false,
                        'validate_rules' => 'a:1:{s:15:"max_text_length";i:255;}',
                        'position' => 100,
                        'admin_checkout' => 1,
                    ],
                    'confirmation' => [
                        'type' => 'varchar',
                        'label' => 'Is Confirmed',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 85,
                        'visible' => false,
                    ],
                    'created_at' => [
                        'type' => 'static',
                        'label' => 'Created At',
                        'input' => 'date',
                        'required' => false,
                        'sort_order' => 86,
                        'visible' => false,
                        'system' => false,
                    ],
                    'gender' => [
                        'type' => 'int',
                        'label' => 'Gender',
                        'input' => 'select',
                        'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                        'required' => false,
                        'sort_order' => 110,
                        'visible' => false,
                        'system' => false,
                        'validate_rules' => 'a:0:{}',
                        'position' => 110,
                        'admin_checkout' => 1,
                        'option' => ['values' => ['Male', 'Female']],
                    ],
                ],
            ],
            'customer_address' => [
                'entity_model' => 'Magento\Customer\Model\Resource\Address',
                'attribute_model' => 'Magento\Customer\Model\Attribute',
                'table' => 'customer_address_entity',
                'additional_attribute_table' => 'customer_eav_attribute',
                'entity_attribute_collection' => 'Magento\Customer\Model\Resource\Address\Attribute\Collection',
                'attributes' => [
                    'prefix' => [
                        'type' => 'varchar',
                        'label' => 'Prefix',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 10,
                        'visible' => false,
                        'system' => false,
                        'position' => 10,
                    ],
                    'firstname' => [
                        'type' => 'varchar',
                        'label' => 'First Name',
                        'input' => 'text',
                        'sort_order' => 20,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 20,
                    ],
                    'middlename' => [
                        'type' => 'varchar',
                        'label' => 'Middle Name/Initial',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 30,
                        'visible' => false,
                        'system' => false,
                        'position' => 30,
                    ],
                    'lastname' => [
                        'type' => 'varchar',
                        'label' => 'Last Name',
                        'input' => 'text',
                        'sort_order' => 40,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 40,
                    ],
                    'suffix' => [
                        'type' => 'varchar',
                        'label' => 'Suffix',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'visible' => false,
                        'system' => false,
                        'position' => 50,
                    ],
                    'company' => [
                        'type' => 'varchar',
                        'label' => 'Company',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 60,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 60,
                    ],
                    'street' => [
                        'type' => 'text',
                        'label' => 'Street Address',
                        'input' => 'multiline',
                        'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend',
                        'sort_order' => 70,
                        'multiline_count' => 2,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 70,
                    ],
                    'city' => [
                        'type' => 'varchar',
                        'label' => 'City',
                        'input' => 'text',
                        'sort_order' => 80,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 80,
                    ],
                    'country_id' => [
                        'type' => 'varchar',
                        'label' => 'Country',
                        'input' => 'select',
                        'source' => 'Magento\Customer\Model\Resource\Address\Attribute\Source\Country',
                        'sort_order' => 90,
                        'position' => 90,
                    ],
                    'region' => [
                        'type' => 'varchar',
                        'label' => 'State/Province',
                        'input' => 'text',
                        'backend' => 'Magento\Customer\Model\Resource\Address\Attribute\Backend\Region',
                        'required' => false,
                        'sort_order' => 100,
                        'position' => 100,
                    ],
                    'region_id' => [
                        'type' => 'int',
                        'label' => 'State/Province',
                        'input' => 'hidden',
                        'source' => 'Magento\Customer\Model\Resource\Address\Attribute\Source\Region',
                        'required' => false,
                        'sort_order' => 100,
                        'position' => 100,
                    ],
                    'postcode' => [
                        'type' => 'varchar',
                        'label' => 'Zip/Postal Code',
                        'input' => 'text',
                        'sort_order' => 110,
                        'validate_rules' => 'a:0:{}',
                        'data' => 'Magento\Customer\Model\Attribute\Data\Postcode',
                        'position' => 110,
                        'required' => false,
                    ],
                    'telephone' => [
                        'type' => 'varchar',
                        'label' => 'Phone Number',
                        'input' => 'text',
                        'sort_order' => 120,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 120,
                    ],
                    'fax' => [
                        'type' => 'varchar',
                        'label' => 'Fax',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 130,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 130,
                    ],
                ],
            ],
        ];
        return $entities;
    }

    /**
     * @return \Magento\Eav\Model\Config
     */
    public function getEavConfig()
    {
        return $this->_eavConfig;
    }
}
