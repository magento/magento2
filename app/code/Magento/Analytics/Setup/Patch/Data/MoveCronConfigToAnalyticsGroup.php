<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Analytics\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Migrate legacy cron config paths for analytics jobs from the "default" group to the "analytics" group.
 */
class MoveCronConfigToAnalyticsGroup implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('core_config_data');

        // Find all analytics cron rows under the "default" cron group
        $select = $connection->select()
            ->from($table, ['config_id', 'scope', 'scope_id', 'path', 'value'])
            ->where('path LIKE ?', 'crontab/default/jobs/analytics_%');
        $rows = (array)$connection->fetchAll($select);

        foreach ($rows as $row) {
            $oldPath = (string)$row['path'];
            $newPath = (string)preg_replace('#^crontab/default/#', 'crontab/analytics/', $oldPath);

            $connection->update(
                $table,
                ['path' => $newPath],
                ['config_id = ?' => (int)$row['config_id']]
            );
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
