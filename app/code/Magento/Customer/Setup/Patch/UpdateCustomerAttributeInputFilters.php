<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpdateCustomerAttributeInputFilters
 * @package Magento\Customer\Setup\Patch
 */
class UpdateCustomerAttributeInputFilters implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * UpdateCustomerAttributeInputFilters constructor.
     * @param ResourceConnection $resourceConnection
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        $entityAttributes = [
            'customer_address' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
            'customer' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
        ];
        $customerSetup->upgradeAttributes($entityAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateVATNumber::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.13';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
