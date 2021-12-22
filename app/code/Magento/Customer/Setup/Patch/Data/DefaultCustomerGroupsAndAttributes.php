<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class default groups and attributes for customer
 */
class DefaultCustomerGroupsAndAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // insert default customer groups
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('customer_group'),
            ['customer_group_id' => 0, 'customer_group_code' => 'NOT LOGGED IN', 'tax_class_id' => 3]
        );
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('customer_group'),
            ['customer_group_id' => 1, 'customer_group_code' => 'General', 'tax_class_id' => 3]
        );
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('customer_group'),
            ['customer_group_id' => 2, 'customer_group_code' => 'Wholesale', 'tax_class_id' => 3]
        );
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('customer_group'),
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
            DefaultBackend::class
        );

        $migrationSetup = $this->moduleDataSetup->createMigrationSetup();

        $migrationSetup->appendClassAliasReplace(
            'customer_eav_attribute',
            'data_model',
            Migration::ENTITY_TYPE_MODEL,
            Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->doUpdateClassAliases();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
