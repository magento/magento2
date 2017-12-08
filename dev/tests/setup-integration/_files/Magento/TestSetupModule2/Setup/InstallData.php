<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestSetupModule2\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->getConnection()->insertForce(
            $setup->getTable('setup_tests_entity_table'),
            [
                'website_id' => 1,
                'email_field' => 'entity@example.com',
                'created_at' => '2017-10-30 09:41:25',
                'updated_at' => '2017-10-30 09:45:05',
                'created_in' => 'Default Store View',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dob' => '1973-12-15',
                'default_billing_address_id' => 1,
                'default_shipping_address_id' => 1
            ]
        );
        $setup->getConnection()->insertForce(
            $setup->getTable('setup_tests_address_entity'),
            [
                'parent_id' => 1,
                'created_at' => '2017-10-30 09:45:05',
                'updated_at' => '2017-10-30 09:45:05',
                'is_active' => 1,
                'city' => 'city',
                'company' => 'Magento',
                'country_id' => 'US',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'postcode' => '90210',
                'region' => 'Alabama',
                'region_id' => 1,
                'street' => 'street1',
                'telephone' => 12345678,
            ]
        );

        $setup->endSetup();
    }
}
