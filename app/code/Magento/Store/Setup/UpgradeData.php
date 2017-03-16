<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var GroupCollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @param GroupCollectionFactory $groupCollectionFactory
     */
    public function __construct(
        GroupCollectionFactory $groupCollectionFactory
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

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
            $this->updateStoreGroupCodes();
        }
    }

    /**
     * Update column 'code' in store_group table.
     *
     * @return void
     */
    private function updateStoreGroupCodes()
    {
        $collection = $this->groupCollectionFactory->create();
        $collection->setLoadDefault(true);

        $codes = [];
        /** @var Group $group */
        foreach ($collection as $group) {
            $code = preg_replace('/[^a-zA-Z0-9-_\s]/', '', strtolower($group->getName()));
            $code = preg_replace('/^[^a-z]+/', '', $code);
            $code = str_replace(' ', '_', $code);

            if (empty($code)) {
                $code = 'store_group';
            }

            if (array_key_exists($code, $codes)) {
                $codes[$code]++;
                $code = $code . $codes[$code];
                $codes[$code] = 1;
            } else {
                $codes[$code] = 1;
            }

            $codes[] = $code;

            $group->setCode($code);
            $group->save();
        }
    }
}
