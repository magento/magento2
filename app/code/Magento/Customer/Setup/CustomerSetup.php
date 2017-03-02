<?php
/**
 * Customer resource setup model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerSetup extends EavSetup
{
    /**
     * EAV configuration
     *
     * @var Config
     */
    protected $eavConfig;

    /**
     * Init
     *
     * @param ModuleDataSetupInterface $setup
     * @param Context $context
     * @param CacheInterface $cache
     * @param CollectionFactory $attrGroupCollectionFactory
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    /**
     * Add customer attributes to customer forms
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function installCustomerForms()
    {
        $customer = (int)parent::getEntityTypeId('customer');
        $customerAddress = (int)parent::getEntityTypeId('customer_address');

        $attributeIds = [];
        $select = $this->getSetup()->getConnection()->select()->from(
            ['ea' => $this->getSetup()->getTable('eav_attribute')],
            ['entity_type_id', 'attribute_code', 'attribute_id']
        )->where(
            'ea.entity_type_id IN(?)',
            [$customer, $customerAddress]
        );
        foreach ($this->getSetup()->getConnection()->fetchAll($select) as $row) {
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
            $this->getSetup()->getConnection()
                ->insertMultiple($this->getSetup()->getTable('customer_form_attribute'), $data);
        }
    }

    /**
     * Retrieve default entities: customer, customer_address
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDefaultEntities()
    {
        $entities = [
            'customer' => [
                'entity_type_id' => \Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'entity_model' => \Magento\Customer\Model\ResourceModel\Customer::class,
                'attribute_model' => \Magento\Customer\Model\Attribute::class,
                'table' => 'customer_entity',
                'increment_model' => \Magento\Eav\Model\Entity\Increment\NumericValue::class,
                'additional_attribute_table' => 'customer_eav_attribute',
                'entity_attribute_collection' => \Magento\Customer\Model\ResourceModel\Attribute\Collection::class,
                'attributes' => [
                    'website_id' => [
                        'type' => 'static',
                        'label' => 'Associate to Website',
                        'input' => 'select',
                        'source' => \Magento\Customer\Model\Customer\Attribute\Source\Website::class,
                        'backend' => \Magento\Customer\Model\Customer\Attribute\Backend\Website::class,
                        'sort_order' => 10,
                        'position' => 10,
                        'adminhtml_only' => 1,
                    ],
                    'store_id' => [
                        'type' => 'static',
                        'label' => 'Create In',
                        'input' => 'select',
                        'source' => \Magento\Customer\Model\Customer\Attribute\Source\Store::class,
                        'backend' => \Magento\Customer\Model\Customer\Attribute\Backend\Store::class,
                        'sort_order' => 20,
                        'visible' => false,
                        'adminhtml_only' => 1,
                    ],
                    'created_in' => [
                        'type' => 'static',
                        'label' => 'Created From',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 20,
                        'position' => 20,
                        'adminhtml_only' => 1,
                    ],
                    'prefix' => [
                        'type' => 'static',
                        'label' => 'Name Prefix',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 30,
                        'visible' => false,
                        'system' => false,
                        'position' => 30,
                    ],
                    'firstname' => [
                        'type' => 'static',
                        'label' => 'First Name',
                        'input' => 'text',
                        'sort_order' => 40,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 40,
                    ],
                    'middlename' => [
                        'type' => 'static',
                        'label' => 'Middle Name/Initial',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'visible' => false,
                        'system' => false,
                        'position' => 50,
                    ],
                    'lastname' => [
                        'type' => 'static',
                        'label' => 'Last Name',
                        'input' => 'text',
                        'sort_order' => 60,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 60,
                    ],
                    'suffix' => [
                        'type' => 'static',
                        'label' => 'Name Suffix',
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
                        'source' => \Magento\Customer\Model\Customer\Attribute\Source\Group::class,
                        'sort_order' => 25,
                        'position' => 25,
                        'adminhtml_only' => 1,
                        'admin_checkout' => 1,
                    ],
                    'dob' => [
                        'type' => 'static',
                        'label' => 'Date of Birth',
                        'input' => 'date',
                        'frontend' => \Magento\Eav\Model\Entity\Attribute\Frontend\Datetime::class,
                        'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class,
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
                        'type' => 'static',
                        'input' => 'hidden',
                        'backend' => \Magento\Customer\Model\Customer\Attribute\Backend\Password::class,
                        'required' => false,
                        'sort_order' => 81,
                        'visible' => false,
                    ],
                    'rp_token' => [
                        'type' => 'static',
                        'input' => 'hidden',
                        'required' => false,
                        'sort_order' => 115,
                        'visible' => false,
                    ],
                    'rp_token_created_at' => [
                        'type' => 'static',
                        'input' => 'date',
                        'validate_rules' => 'a:1:{s:16:"input_validation";s:4:"date";}',
                        'required' => false,
                        'sort_order' => 120,
                        'visible' => false,
                    ],
                    'default_billing' => [
                        'type' => 'static',
                        'label' => 'Default Billing Address',
                        'input' => 'text',
                        'backend' => \Magento\Customer\Model\Customer\Attribute\Backend\Billing::class,
                        'required' => false,
                        'sort_order' => 82,
                        'visible' => false,
                    ],
                    'default_shipping' => [
                        'type' => 'static',
                        'label' => 'Default Shipping Address',
                        'input' => 'text',
                        'backend' => \Magento\Customer\Model\Customer\Attribute\Backend\Shipping::class,
                        'required' => false,
                        'sort_order' => 83,
                        'visible' => false,
                    ],
                    'taxvat' => [
                        'type' => 'static',
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
                        'type' => 'static',
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
                        'type' => 'static',
                        'label' => 'Gender',
                        'input' => 'select',
                        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                        'required' => false,
                        'sort_order' => 110,
                        'visible' => false,
                        'system' => false,
                        'validate_rules' => 'a:0:{}',
                        'position' => 110,
                        'admin_checkout' => 1,
                        'option' => ['values' => ['Male', 'Female']],
                    ],
                    'disable_auto_group_change' => [
                        'type' => 'static',
                        'label' => 'Disable Automatic Group Change Based on VAT ID',
                        'input' => 'boolean',
                        'backend' => \Magento\Customer\Model\Attribute\Backend\Data\Boolean::class,
                        'position' => 28,
                        'required' => false,
                        'adminhtml_only' => true
                    ]
                ],
            ],
            'customer_address' => [
                'entity_type_id' => \Magento\Customer\Api\AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'entity_model' => \Magento\Customer\Model\ResourceModel\Address::class,
                'attribute_model' => \Magento\Customer\Model\Attribute::class,
                'table' => 'customer_address_entity',
                'additional_attribute_table' => 'customer_eav_attribute',
                'entity_attribute_collection' =>
                    \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection::class,
                'attributes' => [
                    'prefix' => [
                        'type' => 'static',
                        'label' => 'Name Prefix',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 10,
                        'visible' => false,
                        'system' => false,
                        'position' => 10,
                    ],
                    'firstname' => [
                        'type' => 'static',
                        'label' => 'First Name',
                        'input' => 'text',
                        'sort_order' => 20,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 20,
                    ],
                    'middlename' => [
                        'type' => 'static',
                        'label' => 'Middle Name/Initial',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 30,
                        'visible' => false,
                        'system' => false,
                        'position' => 30,
                    ],
                    'lastname' => [
                        'type' => 'static',
                        'label' => 'Last Name',
                        'input' => 'text',
                        'sort_order' => 40,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 40,
                    ],
                    'suffix' => [
                        'type' => 'static',
                        'label' => 'Name Suffix',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'visible' => false,
                        'system' => false,
                        'position' => 50,
                    ],
                    'company' => [
                        'type' => 'static',
                        'label' => 'Company',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 60,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 60,
                    ],
                    'street' => [
                        'type' => 'static',
                        'label' => 'Street Address',
                        'input' => 'multiline',
                        'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend::class,
                        'sort_order' => 70,
                        'multiline_count' => 2,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 70,
                    ],
                    'city' => [
                        'type' => 'static',
                        'label' => 'City',
                        'input' => 'text',
                        'sort_order' => 80,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 80,
                    ],
                    'country_id' => [
                        'type' => 'static',
                        'label' => 'Country',
                        'input' => 'select',
                        'source' => \Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country::class,
                        'sort_order' => 90,
                        'position' => 90,
                    ],
                    'region' => [
                        'type' => 'static',
                        'label' => 'State/Province',
                        'input' => 'text',
                        'backend' => \Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region::class,
                        'required' => false,
                        'sort_order' => 100,
                        'position' => 100,
                    ],
                    'region_id' => [
                        'type' => 'static',
                        'label' => 'State/Province',
                        'input' => 'hidden',
                        'source' => \Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Region::class,
                        'required' => false,
                        'sort_order' => 100,
                        'position' => 100,
                    ],
                    'postcode' => [
                        'type' => 'static',
                        'label' => 'Zip/Postal Code',
                        'input' => 'text',
                        'sort_order' => 110,
                        'validate_rules' => 'a:0:{}',
                        'data' => \Magento\Customer\Model\Attribute\Data\Postcode::class,
                        'position' => 110,
                        'required' => false,
                    ],
                    'telephone' => [
                        'type' => 'static',
                        'label' => 'Phone Number',
                        'input' => 'text',
                        'sort_order' => 120,
                        'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
                        'position' => 120,
                    ],
                    'fax' => [
                        'type' => 'static',
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
     * Gets EAV configuration
     *
     * @return Config
     */
    public function getEavConfig()
    {
        return $this->eavConfig;
    }
}
