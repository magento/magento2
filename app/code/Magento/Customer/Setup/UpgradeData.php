<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Framework\Module\Setup\Migration;
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
    private $customerSetupFactory;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $setup->startSetup();

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
                        'is_filterable_in_grid' => false,
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
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'street' => [
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'city' => [
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'country_id' => [
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                    'region' => [
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'region_id' => [
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => false,
                    ],
                    'postcode' => [
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'telephone' => [
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => true,
                    ],
                    'fax' => [
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
        }

        $setup->endSetup();
    }
}
