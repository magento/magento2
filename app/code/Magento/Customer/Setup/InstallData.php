<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     * @since 2.0.0
     */
    private $customerSetupFactory;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @since 2.0.0
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

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

        $customerSetup->installEntities();

        $customerSetup->installCustomerForms();

        $disableAGCAttribute = $customerSetup->getEavConfig()->getAttribute('customer', 'disable_auto_group_change');
        $disableAGCAttribute->setData('used_in_forms', ['adminhtml_customer']);
        $disableAGCAttribute->save();

        $attributesInfo = [
            'vat_id' => [
                'label' => 'VAT number',
                'type' => 'static',
                'input' => 'text',
                'position' => 140,
                'visible' => true,
                'required' => false,
            ],
            'vat_is_valid' => [
                'label' => 'VAT number validity',
                'visible' => false,
                'required' => false,
                'type' => 'static',
            ],
            'vat_request_id' => [
                'label' => 'VAT number validation request ID',
                'type' => 'static',
                'visible' => false,
                'required' => false,
            ],
            'vat_request_date' => [
                'label' => 'VAT number validation request date',
                'type' => 'static',
                'visible' => false,
                'required' => false,
            ],
            'vat_request_success' => [
                'label' => 'VAT number validation request success',
                'visible' => false,
                'required' => false,
                'type' => 'static',
            ],
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute('customer_address', $attributeCode, $attributeParams);
        }

        $vatIdAttribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'vat_id');
        $vatIdAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
        );
        $vatIdAttribute->save();

        $entities = $customerSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $customerSetup->addEntityType($entityName, $entity);
        }

        $customerSetup->updateAttribute(
            'customer_address',
            'street',
            'backend_model',
            \Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend::class
        );

        $migrationSetup = $setup->createMigrationSetup();

        $migrationSetup->appendClassAliasReplace(
            'customer_eav_attribute',
            'data_model',
            Migration::ENTITY_TYPE_MODEL,
            Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->doUpdateClassAliases();

        $setup->endSetup();
    }
}
