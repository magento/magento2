<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch207 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param CustomerSetupFactory $customerSetupFactory @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory,
                                \Magento\Eav\Model\Config $eavConfig)
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $this->upgradeVersionTwoZeroSeven($customerSetup);
        $this->upgradeCustomerPasswordResetlinkExpirationPeriodConfig($setup);


        $this->eavConfig->clear();
        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


    private function upgradeVersionTwoZeroSeven($customerSetup
    )
    {
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

    }

    private function upgradeCustomerPasswordResetlinkExpirationPeriodConfig($setup
    )
    {
        $configTable = $setup->getTable('core_config_data');

        $setup->getConnection()->update(
            $configTable,
            ['value' => new \Zend_Db_Expr('value*24')],
            ['path = ?' => \Magento\Customer\Model\Customer::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD]
        );

    }
}
