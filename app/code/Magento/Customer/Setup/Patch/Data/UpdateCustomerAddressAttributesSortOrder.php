<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update Customer Address Attributes to be displayed in following order: country, region, city, postcode
 */
class UpdateCustomerAddressAttributesSortOrder implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * UpdateCustomerAddressAttributesSortOrder constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->updateCustomerAddressAttributesSortOrder($customerSetup);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [
            DefaultCustomerGroupsAndAttributes::class,
        ];
    }

    /**
     * Update customer address attributes sort order
     *
     * @param CustomerSetup $customerSetup
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function updateCustomerAddressAttributesSortOrder($customerSetup)
    {
        $entityAttributes = [
            'customer_address' => [
                'country_id' => [
                    'sort_order' => 80,
                    'position' => 80
                ],
                'region' => [
                    'sort_order' => 90,
                    'position' => 90
                ],
                'region_id' => [
                    'sort_order' => 90,
                    'position' => 90
                ],
                'city' => [
                    'sort_order' => 100,
                    'position' => 100
                ],
            ],
        ];

        $customerSetup->upgradeAttributes($entityAttributes);
    }
}
