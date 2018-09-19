<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Setup\Patch\Data;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Initial patch.
 *
 * @package Magento\Analytics\Setup\Patch
 */
class PrepareInitialConfig implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PrepareInitialConfig constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->insertMultiple(
            $this->moduleDataSetup->getTable('core_config_data'),
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

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('flag'),
            [
                'flag_code' => SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE,
                'state' => 0,
                'flag_data' => 24,
            ]
        );
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
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
