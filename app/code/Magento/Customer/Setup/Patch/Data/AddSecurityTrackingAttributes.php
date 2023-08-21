<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class add security tracking attributes to customer
 */
class AddSecurityTrackingAttributes implements DataPatchInterface, PatchVersionInterface
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
     * @inheritdoc
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
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
        $configTable = $this->moduleDataSetup->getTable('core_config_data');

        $this->moduleDataSetup->getConnection()->update(
            $configTable,
            ['value' => new \Zend_Db_Expr('value*24')],
            ['path = ?' => Customer::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            RemoveCheckoutRegisterAndUpdateAttributes::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.7';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
