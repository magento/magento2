<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Mview\Config;
use Magento\Framework\Phrase;

/**
 * Interface \Magento\Framework\Mview\View\ChangeLogBatchIterator
 *
 */
class ChangeLogBatchIterator implements ChangeLogBatchIteratorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Config
     */
    private $mviewConfig;

    /**
     * ChangeLogBatchIterator constructor.
     * @param ResourceConnection $resourceConnection
     * @param Config $mviewConfig
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Config $mviewConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->mviewConfig = $mviewConfig;
    }

    /**
     * Walk through batches
     *
     * @param array $changeLogData
     * @param $fromVersionId
     * @param int $batchSize
     * @return mixed
     * @throws ChangelogTableNotExistsException
     */
    public function walk(array $changeLogData, $fromVersionId, int $batchSize)
    {
        $configuration = $this->mviewConfig->getView($changeLogData['view_id']);
        $connection = $this->resourceConnection->getConnection();
        $changelogTableName = $this->resourceConnection->getTableName($changeLogData['name']);
        if (!$connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }
        $columns = [$changeLogData['column_name']];
        $select = $connection->select()->distinct(true)
            ->where(
                'version_id > ?',
                (int)$fromVersionId
            )
            ->group([$changeLogData['column_name'], 'store_id'])
            ->limit($batchSize);

        $columns = [
            $changeLogData['column_name'],
            'attribute_ids' => new Expression('GROUP_CONCAT(attribute_id)'),
            'store_id'
        ];

        $select->from($changelogTableName, $columns);
        return $connection->fetchAll($select);
    }
}
