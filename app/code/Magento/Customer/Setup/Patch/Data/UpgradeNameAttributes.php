<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpgradeNameAttributes
 * @package Magento\Customer\Setup\Patch
 */
class UpgradeNameAttributes implements DataPatchInterface, PatchVersionInterface
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
     * UpgradeNameAttributes constructor.
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
     * {@inheritdoc}
     */
    public function apply()
    {
        $entityAttributes = [
            'customer_address' => [
                'firstname' => [
                    'frontend_class' => 'validate-alpha',
                ],
                'middlename' => [
                    'frontend_class' => 'validate-alpha',
                ],
                'lastname' => [
                    'frontend_class' => 'validate-alpha',
                ],
            ],
            'customer' => [
                'firstname' => [
                    'frontend_class' => 'validate-alpha',
                ],
                'middlename' => [
                    'frontend_class' => 'validate-alpha',
                ],
                'lastname' => [
                    'frontend_class' => 'validate-alpha',
                ],
            ],
        ];
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->upgradeAttributes($entityAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddCustomerUpdatedAtAttribute::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.5';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
