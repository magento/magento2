<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$bunches = array(
    0 => array(
        'entity' => 'customer',
        'behavior' => 'v2_update',
        'data' => array(
            0 => array(
                'email' => 'AnthonyANealy@magento.com',
                '_website' => 'base',
                '_store' => 'admin',
                'confirmation' => null,
                'created_at' => '05-06-12 15:53',
                'created_in' => 'Admin',
                'default_billing' => '1',
                'default_shipping' => '1',
                'disable_auto_group_change' => '0',
                'dob' => '13-06-1984',
                'firstname' => 'Anthony',
                'gender' => 'Male',
                'group_id' => '1',
                'lastname' => 'Nealy',
                'middlename' => 'A.',
                'password_hash' => '6a9c9bfb2ba88a6ad2a64e7402df44a763e0c48cd21d7af9e7e796cd4677ee28:RF',
                'prefix' => null,
                'reward_update_notification' => '1',
                'reward_warning_notification' => '1',
                'rp_token' => null,
                'rp_token_created_at' => null,
                'store_id' => '0',
                'suffix' => null,
                'taxvat' => null,
                'website_id' => '1',
                'password' => null
            ),
            1 => array(
                'email' => 'LoriBBanks@magento.com',
                '_website' => 'admin',
                '_store' => 'admin',
                'confirmation' => null,
                'created_at' => '05-06-12 15:59',
                'created_in' => 'Admin',
                'default_billing' => '3',
                'default_shipping' => '3',
                'disable_auto_group_change' => '0',
                'dob' => null,
                'firstname' => 'Lori',
                'gender' => 'Female',
                'group_id' => '1',
                'lastname' => 'Banks',
                'middlename' => null,
                'password_hash' => '7ad6dbdc83d3e9f598825dc58b84678c7351e4281f6bc2b277a32dcd88b9756b:pz',
                'prefix' => null,
                'reward_update_notification' => '1',
                'reward_warning_notification' => '1',
                'rp_token' => null,
                'rp_token_created_at' => null,
                'store_id' => '0',
                'suffix' => null,
                'taxvat' => null,
                'website_id' => '0',
                'password' => null
            )
        )
    ),
    1 => array(
        'entity' => 'customer',
        'behavior' => 'v2_update',
        'data' => array(
            0 => array(
                'email' => 'BetsyHParker@magento.com',
                '_website' => 'base',
                '_store' => 'admin',
                'confirmation' => null,
                'created_at' => '05-06-12 16:13',
                'created_in' => 'Admin',
                'default_billing' => '4',
                'default_shipping' => '4',
                'disable_auto_group_change' => '0',
                'dob' => null,
                'firstname' => 'Betsy',
                'gender' => 'Female',
                'group_id' => '1',
                'lastname' => 'Parker',
                'middlename' => 'H.',
                'password_hash' => '145d12bfff8a6a279eb61e277e3d727c0ba95acc1131237f1594ddbb7687a564:l1',
                'prefix' => null,
                'reward_update_notification' => '1',
                'reward_warning_notification' => '1',
                'rp_token' => null,
                'rp_token_created_at' => null,
                'store_id' => '0',
                'suffix' => null,
                'taxvat' => null,
                'website_id' => '2',
                'password' => null
            )
        )
    )
);

$importDataResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\ImportExport\Model\Resource\Import\Data'
);

foreach ($bunches as $bunch) {
    $importDataResource->saveBunch($bunch['entity'], $bunch['behavior'], $bunch['data']);
}

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->get('Magento\Framework\Registry')->unregister('_fixture/Magento_ImportExport_Import_Data');
$objectManager->get('Magento\Framework\Registry')->register('_fixture/Magento_ImportExport_Import_Data', $bunches);
