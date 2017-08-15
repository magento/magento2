<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Upgrades data for a Store module.
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->updateStoreGroupCodes($setup);
        }
    }

    /**
     * Update column 'code' in store_group table.
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function updateStoreGroupCodes($setup)
    {
        $storeGroupTable = $setup->getTable('store_group');
        $select = $setup->getConnection()->select()->from(
            $storeGroupTable,
            ['group_id', 'name']
        );

        $groupList = $setup->getConnection()->fetchPairs($select);

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

            $setup->getConnection()->update(
                $storeGroupTable,
                ['code' => $code],
                ['group_id = ?' => $groupId]
            );
        }
    }
}
