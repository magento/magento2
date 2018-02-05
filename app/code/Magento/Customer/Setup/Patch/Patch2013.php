<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch2013
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

        $this->upgradeVersionTwoZeroThirteen($customerSetup);


        $this->eavConfig->clear();
        $setup->endSetup();

    }

    private function upgradeVersionTwoZeroThirteen(CustomerSetup $customerSetup
    )
    {
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
        $this->upgradeAttributes($entityAttributes, $customerSetup);

    }

    private function upgradeAttributes(array $entityAttributes, CustomerSetup $customerSetup
    )
    {
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
}
