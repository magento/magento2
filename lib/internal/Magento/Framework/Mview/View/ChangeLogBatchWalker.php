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
     * @var ChangelogInterface
     */
    private $changelog;
    /**
     * @var int
     */
    private $fromVersionId;

    /**
     * @var int
     */
    private $toVersionId;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ChangelogInterface $changelog
     * @param int $fromVersionId
     * @param int $toVersionId
     * @param int $batchSize
     * @param array $entityTypeCodes
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ChangelogInterface $changelog,
        int $fromVersionId,
        int $toVersionId,
        int $batchSize
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->changelog = $changelog;
        $this->fromVersionId = $fromVersionId;
        $this->toVersionId = $toVersionId;
        $this->batchSize = $batchSize;
    }

    /**
     * @return \Generator|\Traversable
     * @throws \Exception
     */
    public function getIterator(): \Generator
    {
        while ($this->fromVersionId < $this->toVersionId) {
            $ids = $this->walk();

            if (empty($ids)) {
                break;
            }
            $this->fromVersionId += $this->batchSize;
            yield $ids;
        }
    }

    /**
     * @inheritdoc
     */
    private function walk()
    {
        $connection = $this->resourceConnection->getConnection();
        $changelogTableName = $this->resourceConnection->getTableName($this->changelog->getName());

        $select = $connection->select()->distinct(true)
            ->where(
                'version_id > ?',
                $this->fromVersionId
            )
            ->where(
                'version_id <= ?',
                $this->toVersionId
            )
            ->group([$this->changelog->getColumnName()])
            ->limit($this->batchSize);

        $select->from($changelogTableName, [$this->changelog->getColumnName()]);
        return $connection->fetchCol($select);
    }
}
