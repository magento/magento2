<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Setup;

use Magento\Customer\Setup\Customer\Setup;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataResourceInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var Setup
     */
    private $customerSetup;

    public function __construct(Setup $customerSetup)
    {
        $this->customerSetup = $customerSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataResourceInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // insert default customer groups
        $setup->getConnection()->insertForce(
            $setup->getTable('customer_group'),
            ['customer_group_id' => 0, 'customer_group_code' => 'NOT LOGGED IN', 'tax_class_id' => 3]
        );
        $setup->getConnection()->insertForce(
            $setup->getTable('customer_group'),
            ['customer_group_id' => 1, 'customer_group_code' => 'General', 'tax_class_id' => 3]
        );
        $setup->getConnection()->insertForce(
            $setup->getTable('customer_group'),
            ['customer_group_id' => 2, 'customer_group_code' => 'Wholesale', 'tax_class_id' => 3]
        );
        $setup->getConnection()->insertForce(
            $setup->getTable('customer_group'),
            ['customer_group_id' => 3, 'customer_group_code' => 'Retailer', 'tax_class_id' => 3]
        );

        $this->customerSetup->installEntities();

        $this->customerSetup->installCustomerForms();

        // Add reset password link token attribute
        $this->customerSetup->addAttribute(
            'customer',
            'rp_token',
            ['type' => 'varchar', 'input' => 'hidden', 'visible' => false, 'required' => false]
        );

        // Add reset password link token creation date attribute
        $this->customerSetup->addAttribute(
            'customer',
            'rp_token_created_at',
            [
                'type' => 'datetime',
                'input' => 'date',
                'validate_rules' => 'a:1:{s:16:"input_validation";s:4:"date";}',
                'visible' => false,
                'required' => false
            ]
        );

        // Add VAT attributes to customer address
        $disableAGCAttributeCode = 'disable_auto_group_change';

        $this->customerSetup->addAttribute(
            'customer',
            $disableAGCAttributeCode,
            [
                'type' => 'static',
                'label' => 'Disable Automatic Group Change Based on VAT ID',
                'input' => 'boolean',
                'backend' => 'Magento\Customer\Model\Attribute\Backend\Data\Boolean',
                'position' => 28,
                'required' => false
            ]
        );

        $disableAGCAttribute = $this->customerSetup->getEavConfig()->getAttribute('customer', $disableAGCAttributeCode);
        $disableAGCAttribute->setData('used_in_forms', ['adminhtml_customer']);
        $disableAGCAttribute->save();

        $attributesInfo = [
            'vat_id' => [
                'label' => 'VAT number',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 140,
                'visible' => true,
                'required' => false,
            ],
            'vat_is_valid' => [
                'label' => 'VAT number validity',
                'visible' => false,
                'required' => false,
                'type' => 'int',
            ],
            'vat_request_id' => [
                'label' => 'VAT number validation request ID',
                'type' => 'varchar',
                'visible' => false,
                'required' => false,
            ],
            'vat_request_date' => [
                'label' => 'VAT number validation request date',
                'type' => 'varchar',
                'visible' => false,
                'required' => false,
            ],
            'vat_request_success' => [
                'label' => 'VAT number validation request success',
                'visible' => false,
                'required' => false,
                'type' => 'int',
            ],
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $this->customerSetup->addAttribute('customer_address', $attributeCode, $attributeParams);
        }

        $vatIdAttribute = $this->customerSetup->getEavConfig()->getAttribute('customer_address', 'vat_id');
        $vatIdAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
        );
        $vatIdAttribute->save();

        $entities = $this->customerSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $this->customerSetup->addEntityType($entityName, $entity);
        }

        $this->customerSetup->updateAttribute(
            'customer_address',
            'street',
            'backend_model',
            'Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend'
        );

        $migrationSetup = $setup->createMigrationSetup();

        $migrationSetup->appendClassAliasReplace(
            'customer_eav_attribute',
            'data_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->doUpdateClassAliases();

        $migrationSetup->endSetup();
    }
}
