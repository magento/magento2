<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Phrase;

/**
 * Interface \Magento\Framework\Mview\View\ChangeLogBatchWalkerInterface
 *
 */
class ChangeLogBatchWalker implements ChangeLogBatchWalkerInterface
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
     * @inheritdoc
     */
    public function walk(ChangelogInterface $changelog, int $fromVersionId, int $toVersion, int $batchSize)
    {
        $connection = $this->resourceConnection->getConnection();
        $changelogTableName = $this->resourceConnection->getTableName($changelog->getName());

        if (!$connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }

        $select = $connection->select()->distinct(true)
            ->where(
                'version_id > ?',
                $fromVersionId
            )
            ->where(
                'version_id <= ?',
                $toVersion
            )
            ->group([$changelog->getColumnName()])
            ->limit($batchSize);

        $select->from($changelogTableName, [$changelog->getColumnName()]);
        return $connection->fetchCol($select);
    }
}
