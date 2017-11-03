<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestSetupModule2\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // data update for TestSetupModule2 module < 0.0.2
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            // add one more row to address_entity table
            $setup->getConnection()->insertForce(
                $setup->getTable('setup_tests_address_entity'),
                [
                    'parent_id' => 1,
                    'created_at' => '2017-10-30 13:34:19',
                    'updated_at' => '2017-10-30 13:34:19',
                    'is_active' => 1,
                    'city' => 'Austin',
                    'company' => 'X.Commerce',
                    'country_id' => 'US',
                    'firstname' => 'Joan',
                    'lastname' => 'Doe',
                    'postcode' => '36351',
                    'region' => 'Alabama',
                    'region_id' => 1,
                    'street' => 'New Brockton',
                    'telephone' => 12345678,
                ]
            );
            $setup->getConnection()->update($setup->getTable('setup_tests_entity_table'), [
                'increment_id'=> 1
            ], 'increment_id = null');

            $setup->getConnection()->insertForce(
                $setup->getTable('setup_tests_entity_passwords'),
                [
                    'entity_id' => 1,
                    'password_hash' => '139e2ee2785cd9d9eb5714a02aca579bbcc05f9062996389d6e0e329bab9841b',
                ]
            );
        }
        $setup->endSetup();
    }
}
