<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Setup\Patch;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial implements \Magento\Setup\Model\Patch\DataPatchInterface
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
        $setup->getConnection()->insertMultiple(
            $setup->getTable('core_config_data'),
            [
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => 'analytics/subscription/enabled',
                    'value' => 1
                ],
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => SubscriptionHandler::CRON_STRING_PATH,
                    'value' => join(' ', SubscriptionHandler::CRON_EXPR_ARRAY)
                ]
            ]
        );

        $setup->getConnection()->insert(
            $setup->getTable('flag'),
            [
                'flag_code' => SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE,
                'state' => 0,
                'flag_data' => 24,
            ]
        );

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


}
