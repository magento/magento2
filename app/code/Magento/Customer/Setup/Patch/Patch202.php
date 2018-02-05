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
class Patch202
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

        $this->upgradeVersionTwoZeroTwo($customerSetup);


        $this->eavConfig->clear();
        $setup->endSetup();

    }

    private function upgradeVersionTwoZeroTwo($customerSetup
    )
    {
        $entityTypeId = $customerSetup->getEntityTypeId(Customer::ENTITY);
        $attributeId = $customerSetup->getAttributeId($entityTypeId, 'gender');

        $option = ['attribute_id' => $attributeId, 'values' => [3 => 'Not Specified']];
        $customerSetup->addAttributeOption($option);

    }
}
