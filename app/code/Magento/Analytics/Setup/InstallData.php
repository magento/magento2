<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Setup;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
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
}
