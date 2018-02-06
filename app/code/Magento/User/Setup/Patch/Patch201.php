<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Setup\Patch;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


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

        $this->upgradeHash($setup);


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


    private function upgradeHash($setup
    )
    {
        $customerEntityTable = $setup->getTable('admin_user');

        $select = $setup->getConnection()->select()->from(
            $customerEntityTable,
            ['user_id', 'password']
        );

        $customers = $setup->getConnection()->fetchAll($select);
        foreach ($customers as $customer) {
            list($hash, $salt) = explode(Encryptor::DELIMITER, $customer['password']);

            $newHash = $customer['password'];
            if (strlen($hash) === 32) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_MD5]);
            } elseif (strlen($hash) === 64) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_SHA256]);
            }

            $bind = ['password' => $newHash];
            $where = ['user_id = ?' => (int)$customer['user_id']];
            $setup->getConnection()->update($customerEntityTable, $bind, $where);
        }

    }
}
