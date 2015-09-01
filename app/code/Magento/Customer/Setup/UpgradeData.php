<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Customer\Model\Customer;
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

            foreach ($entityAttributes as $entityType => $attributes) {
                foreach ($attributes as $attributeCode => $attributeData) {
                    $attribute = $customerSetup->getEavConfig()->getAttribute($entityType, $attributeCode);
                    foreach ($attributeData as $key => $value) {
                        $attribute->setData($key, $value);
                    }
                    $attribute->save();
                }
            }
            $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
            $indexer->reindexAll();
            $this->eavConfig->clear();
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $entityTypeId = $customerSetup->getEntityTypeId(Customer::ENTITY);
            $attributeId = $customerSetup->getAttributeId($entityTypeId, 'gender');

            $option = ['attribute_id' => $attributeId, 'values' => [3 => 'Not Specified']];
            $customerSetup->addAttributeOption($option);
        }

        $setup->endSetup();
    }
}
