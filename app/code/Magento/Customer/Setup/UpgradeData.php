<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param IndexerRegistry $indexerRegistry
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        IndexerRegistry $indexerRegistry,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $customerSetup->updateEntityType(
                \Magento\Customer\Model\Customer::ENTITY,
                'entity_model',
                'Magento\Customer\Model\ResourceModel\Customer'
            );
            $customerSetup->updateEntityType(
                \Magento\Customer\Model\Customer::ENTITY,
                'increment_model',
                'Magento\Eav\Model\Entity\Increment\NumericValue'
            );
            $customerSetup->updateEntityType(
                \Magento\Customer\Model\Customer::ENTITY,
                'entity_attribute_collection',
                'Magento\Customer\Model\ResourceModel\Attribute\Collection'
            );
            $customerSetup->updateEntityType(
                'customer_address',
                'entity_model',
                'Magento\Customer\Model\ResourceModel\Address'
            );
            $customerSetup->updateEntityType(
                'customer_address',
                'entity_attribute_collection',
                'Magento\Customer\Model\ResourceModel\Address\Attribute\Collection'
            );
            $customerSetup->updateAttribute(
                'customer_address',
                'country_id',
                'source_model',
                'Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country'
            );
            $customerSetup->updateAttribute(
                'customer_address',
                'region',
                'backend_model',
                'Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region'
            );
            $customerSetup->updateAttribute(
                'customer_address',
                'region_id',
                'source_model',
                'Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Region'
            );
        }

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $entityAttributes = [
                'customer' => [
                    'website_id' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                    'created_in' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'email' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => true,
                    ],
                    'group_id' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                    'dob' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                    'taxvat' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'confirmation' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                    'created_at' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                    'gender' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                ],
                'customer_address' => [
                    'company' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'street' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'city' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'country_id' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                    'region' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'region_id' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                    'postcode' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => true,
                    ],
                    'telephone' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => true,
                    ],
                    'fax' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                ],
            ];
            $this->upgradeAttributes($entityAttributes, $customerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $entityTypeId = $customerSetup->getEntityTypeId(Customer::ENTITY);
            $attributeId = $customerSetup->getAttributeId($entityTypeId, 'gender');

            $option = ['attribute_id' => $attributeId, 'values' => [3 => 'Not Specified']];
            $customerSetup->addAttributeOption($option);
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $entityAttributes = [
                'customer_address' => [
                    'region_id' => [
                        'is_used_in_grid' => false,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => false,
                    ],
                    'firstname' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'lastname' => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                ],
            ];
            $this->upgradeAttributes($entityAttributes, $customerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'updated_at',
                [
                    'type' => 'static',
                    'label' => 'Updated At',
                    'input' => 'date',
                    'required' => false,
                    'sort_order' => 87,
                    'visible' => false,
                    'system' => false,
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->upgradeHash($setup);
            $entityAttributes = [
                'customer_address' => [
                    'fax' => [
                        'is_visible' => false,
                        'is_system' => false,
                    ],
                ],
            ];
            $this->upgradeAttributes($entityAttributes, $customerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $setup->getConnection()->delete(
                $setup->getTable('customer_form_attribute'),
                ['form_code = ?' => 'checkout_register']
            );
        }

        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $this->eavConfig->clear();
        $setup->endSetup();
    }

    /**
     * @param array $entityAttributes
     * @param CustomerSetup $customerSetup
     * @return void
     */
    protected function upgradeAttributes(array $entityAttributes, CustomerSetup $customerSetup)
    {
        foreach ($entityAttributes as $entityType => $attributes) {
            foreach ($attributes as $attributeCode => $attributeData) {
                $attribute = $customerSetup->getEavConfig()->getAttribute($entityType, $attributeCode);
                foreach ($attributeData as $key => $value) {
                    $attribute->setData($key, $value);
                }
                $attribute->save();
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeHash($setup)
    {
        $customerEntityTable = $setup->getTable('customer_entity');

        $select = $setup->getConnection()->select()->from(
            $customerEntityTable,
            ['entity_id', 'password_hash']
        );

        $customers = $setup->getConnection()->fetchAll($select);
        foreach ($customers as $customer) {
            if ($customer['password_hash'] === null) {
                continue;
            }
            list($hash, $salt) = explode(Encryptor::DELIMITER, $customer['password_hash']);

            $newHash = $customer['password_hash'];
            if (strlen($hash) === 32) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_MD5]);
            } elseif (strlen($hash) === 64) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_SHA256]);
            }

            $bind = ['password_hash' => $newHash];
            $where = ['entity_id = ?' => (int)$customer['entity_id']];
            $setup->getConnection()->update($customerEntityTable, $bind, $where);
        }
    }
}
