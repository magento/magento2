<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade data script for Integration module
 * @since 2.2.0
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->removeRevokedTokens($setup);
            $this->removeTokensFromInactiveAdmins($setup);
            $this->removeTokensFromInactiveCustomers($setup);
        }

        $setup->endSetup();
    }

    /**
     * Remove any revoked tokens from oauth_token table
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     * @since 2.2.0
     */
    private function removeRevokedTokens($setup)
    {
        $oauthTokenTable = $setup->getTable('oauth_token');

        $where = ['revoked = ?' => 1];
        $setup->getConnection()->delete($oauthTokenTable, $where);
    }

    /**
     * Remove any tokens from oauth_token table where admin is inactive
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     * @since 2.2.0
     */
    private function removeTokensFromInactiveAdmins($setup)
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

    /**
     * Remove any tokens from oauth_token table where customer is inactive
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     * @since 2.2.0
     */
    private function removeTokensFromInactiveCustomers($setup)
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
