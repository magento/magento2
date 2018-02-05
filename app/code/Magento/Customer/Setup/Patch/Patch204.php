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
class Patch204
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
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $this->upgradeVersionTwoZeroFour($customerSetup);


        $this->eavConfig->clear();
        $setup->endSetup();

    }

    private function upgradeVersionTwoZeroFour($customerSetup
    )
    {
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
}
