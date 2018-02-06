<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch210 implements \Magento\Setup\Model\Patch\DataPatchInterface
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
        $this->updateStoreGroupCodes($setup);

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


    private function updateStoreGroupCodes($setup
    )
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
