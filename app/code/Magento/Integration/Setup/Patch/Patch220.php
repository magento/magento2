<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch220
{


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

        $this->removeRevokedTokens($setup);
        $this->removeTokensFromInactiveAdmins($setup);
        $this->removeTokensFromInactiveCustomers($setup);

        $setup->endSetup();

    }

    private function removeRevokedTokens($setup
    )
    {
        $oauthTokenTable = $setup->getTable('oauth_token');

        $where = ['revoked = ?' => 1];
        $setup->getConnection()->delete($oauthTokenTable, $where);

    }

    private function removeTokensFromInactiveAdmins($setup
    )
    {
        $oauthTokenTable = $setup->getTable('oauth_token');
        $adminUserTable = $setup->getTable('admin_user');

        $select = $setup->getConnection()->select()->from(
            $adminUserTable,
            ['user_id', 'is_active']
        );

        $admins = $setup->getConnection()->fetchAll($select);
        foreach ($admins as $admin) {
            if ($admin['is_active'] == 0) {
                $where = ['admin_id = ?' => (int)$admin['user_id']];
                $setup->getConnection()->delete($oauthTokenTable, $where);
            }
        }

    }

    private function removeTokensFromInactiveCustomers($setup
    )
    {
        $oauthTokenTable = $setup->getTable('oauth_token');
        $adminUserTable = $setup->getTable('customer_entity');

        $select = $setup->getConnection()->select()->from(
            $adminUserTable,
            ['entity_id', 'is_active']
        );

        $admins = $setup->getConnection()->fetchAll($select);
        foreach ($admins as $admin) {
            if ($admin['is_active'] == 0) {
                $where = ['customer_id = ?' => (int)$admin['entity_id']];
                $setup->getConnection()->delete($oauthTokenTable, $where);
            }
        }

    }
}
