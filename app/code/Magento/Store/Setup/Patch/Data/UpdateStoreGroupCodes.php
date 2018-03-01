<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdateStoreGroupCodes
 * @package Magento\Store\Setup\Patch
 */
class UpdateStoreGroupCodes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * UpdateStoreGroupCodes constructor.
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
        $this->updateStoreGroupCodes();
    }

    /**
     * Upgrade codes for store groups
     */
    private function updateStoreGroupCodes()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $storeGroupTable = $this->moduleDataSetup->getTable('store_group');
        $select = $connection->select()->from(
            $storeGroupTable,
            ['group_id', 'name']
        );

        $groupList = $connection->fetchPairs($select);

        $codes = [];
        foreach ($groupList as $groupId => $groupName) {
            $code = preg_replace('/\s+/', '_', $groupName);
            $code = preg_replace('/[^a-z0-9-_]/', '', strtolower($code));
            $code = preg_replace('/^[^a-z]+/', '', $code);

            if (empty($code)) {
                $code = 'store_group';
            }

            if (array_key_exists($code, $codes)) {
                $codes[$code]++;
                $code = $code . $codes[$code];
            }
            $codes[$code] = 1;

            $connection->update(
                $storeGroupTable,
                ['code' => $code],
                ['group_id = ?' => $groupId]
            );
        }
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
        return '2.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
