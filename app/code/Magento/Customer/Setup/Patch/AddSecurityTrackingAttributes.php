<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\VersionedDataPatch;

/**
 * Class AddSecurityTrackingAttributes
 * @package Magento\Customer\Setup\Patch
 */
class AddSecurityTrackingAttributes implements DataPatchInterface, VersionedDataPatch
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
     * AddSecurityTrackingAttributes constructor.
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
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'failures_num',
            [
                'type' => 'static',
                'label' => 'Failures Number',
                'input' => 'hidden',
                'required' => false,
                'sort_order' => 100,
                'visible' => false,
                'system' => true,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'first_failure',
            [
                'type' => 'static',
                'label' => 'First Failure Date',
                'input' => 'date',
                'required' => false,
                'sort_order' => 110,
                'visible' => false,
                'system' => true,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'lock_expires',
            [
                'type' => 'static',
                'label' => 'Failures Number',
                'input' => 'date',
                'required' => false,
                'sort_order' => 120,
                'visible' => false,
                'system' => true,
            ]
        );
        $configTable = $this->resourceConnection->getConnection()->getTableName('core_config_data');

        $this->resourceConnection->getConnection()->update(
            $configTable,
            ['value' => new \Zend_Db_Expr('value*24')],
            ['path = ?' => \Magento\Customer\Model\Customer::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            RemoveCheckoutRegisterAndUpdateAttributes::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.7';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
