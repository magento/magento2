<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class RemoveInactiveTokens
 * @package Magento\Integration\Setup\Patch
 */
class RemoveInactiveTokens implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();

        $this->removeRevokedTokens();
        $this->removeTokensFromInactiveAdmins();
        $this->removeTokensFromInactiveCustomers();

        $this->resourceConnection->getConnection()->endSetup();

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
    public function getVersion()
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
        $oauthTokenTable = $this->resourceConnection->getConnection()->getTableName('oauth_token');

        $where = ['revoked = ?' => 1];
        $this->resourceConnection->getConnection()->delete($oauthTokenTable, $where);
    }

    /**
     * Remove inactive admin users tokens
     *
     * @return void
     */
    private function removeTokensFromInactiveAdmins()
    {
        $oauthTokenTable = $this->resourceConnection->getConnection()->getTableName('oauth_token');
        $adminUserTable = $this->resourceConnection->getConnection()->getTableName('admin_user');

        $select = $this->resourceConnection->getConnection()->select()->from(
            $adminUserTable,
            ['user_id', 'is_active']
        );

        $admins = $this->resourceConnection->getConnection()->fetchAll($select);
        foreach ($admins as $admin) {
            if ($admin['is_active'] == 0) {
                $where = ['admin_id = ?' => (int)$admin['user_id']];
                $this->resourceConnection->getConnection()->delete($oauthTokenTable, $where);
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
        $oauthTokenTable = $this->resourceConnection->getConnection()->getTableName('oauth_token');
        $adminUserTable = $this->resourceConnection->getConnection()->getTableName('customer_entity');

        $select = $this->resourceConnection->getConnection()->select()->from(
            $adminUserTable,
            ['entity_id', 'is_active']
        );

        $admins = $this->resourceConnection->getConnection()->fetchAll($select);
        foreach ($admins as $admin) {
            if ($admin['is_active'] == 0) {
                $where = ['customer_id = ?' => (int)$admin['entity_id']];
                $this->resourceConnection->getConnection()->delete($oauthTokenTable, $where);
            }
        }

    }
}
