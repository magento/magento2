<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Setup\Patch;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\VersionedDataPatch;

/**
 * Initial patch.
 *
 * @package Magento\Analytics\Setup\Patch
 */
class PrepareInitialConfig implements DataPatchInterface, VersionedDataPatch
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * PrepareInitialConfig constructor.
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
        $this->resourceConnection->getConnection()->insertMultiple(
            $this->resourceConnection->getConnection()->getTableName('core_config_data'),
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

        $this->resourceConnection->getConnection()->insert(
            $this->resourceConnection->getConnection()->getTableName('flag'),
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
    public function getVersion()
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
