<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class RemoveInactiveTokens
 * @package Magento\Integration\Setup\Patch
 */
class RemoveInactiveTokens implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->removeRevokedTokens();
        $this->removeTokensFromInactiveAdmins();
        $this->removeTokensFromInactiveCustomers();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.2.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Remove revoked tokens.
     *
     * @return void
     */
    private function removeRevokedTokens()
    {
        $oauthTokenTable = $this->moduleDataSetup->getTable('oauth_token');

        $where = ['revoked = ?' => 1];
        $this->moduleDataSetup->getConnection()->delete($oauthTokenTable, $where);
    }

    /**
     * Remove inactive admin users tokens
     *
     * @return void
     */
    private function removeTokensFromInactiveAdmins()
    {
        $oauthTokenTable = $this->moduleDataSetup->getTable('oauth_token');
        $adminUserTable = $this->moduleDataSetup->getTable('admin_user');

        $select = $this->moduleDataSetup->getConnection()->select()->from(
            $adminUserTable,
            ['user_id', 'is_active']
        );

        $admins = $this->moduleDataSetup->getConnection()->fetchAll($select);
        foreach ($admins as $admin) {
            if ($admin['is_active'] == 0) {
                $where = ['admin_id = ?' => (int)$admin['user_id']];
                $this->moduleDataSetup->getConnection()->delete($oauthTokenTable, $where);
            }
        }
    }

    /**
     * Remove tokens for inactive customers
     *
     * @return void
     */
    private function removeTokensFromInactiveCustomers()
    {
        $oauthTokenTable = $this->moduleDataSetup->getTable('oauth_token');
        $adminUserTable = $this->moduleDataSetup->getTable('customer_entity');

        $select = $this->moduleDataSetup->getConnection()->select()->from(
            $adminUserTable,
            ['entity_id', 'is_active']
        );

        $admins = $this->moduleDataSetup->getConnection()->fetchAll($select);
        foreach ($admins as $admin) {
            if ($admin['is_active'] == 0) {
                $where = ['customer_id = ?' => (int)$admin['entity_id']];
                $this->moduleDataSetup->getConnection()->delete($oauthTokenTable, $where);
            }
        }
    }
}
