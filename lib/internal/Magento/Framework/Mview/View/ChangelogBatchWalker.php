<?php declare(strict_types=1);
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsContext;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsSelectBuilderInterface;
use Magento\Framework\Phrase;

/**
 * Walk changelog entries in bounded, cursor-aware batches.
 */
class ChangelogBatchWalker implements ChangelogBatchWalkerInterface
{
    private const CURSOR_COLUMN = 'max_version_id';

    /** @var ResourceConnection */
    private ResourceConnection $resourceConnection;

    /** @var IdsSelectBuilderInterface */
    private IdsSelectBuilderInterface $idsSelectBuilder;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Generator $generator
     * @param IdsContext $idsContext
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Generator $generator,
        IdsContext $idsContext
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->idsSelectBuilder = $idsContext->getSelectBuilder();
    }

    /**
     * @inheritdoc
     */
    public function walk(
        ChangelogInterface $changelog,
        int $fromVersionId,
        int $lastVersionId,
        int $batchSize
    ): iterable {
        foreach ($this->walkWithCursor($changelog, $fromVersionId, $lastVersionId, $batchSize) as $batch) {
            yield $batch['ids'];
        }
    }

    /**
     * @inheritdoc
     */
    public function walkWithCursor(
        ChangelogInterface $changelog,
        int $fromVersionId,
        int $lastVersionId,
        int $batchSize
    ): iterable {
        $connection = $this->resourceConnection->getConnection();
        $changelogTableName = $this->resourceConnection->getTableName($changelog->getName());

        if (!$connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }

        if ($fromVersionId >= $lastVersionId) {
            return;
        }

        $baseSelect = $this->idsSelectBuilder->build($changelog);
        $groupedColumns = $baseSelect->getPart(Select::GROUP);
        $hasCustomGrouping = !empty($groupedColumns);
        if (!in_array($changelog->getColumnName(), $groupedColumns, true)) {
            $baseSelect->group($changelog->getColumnName());
        }
        $baseSelect->columns([
            self::CURSOR_COLUMN => new Expression('MAX(version_id)'),
        ])
            ->order(self::CURSOR_COLUMN . ' ASC');

        $cursorVersionId = $fromVersionId;
        $processId = getmypid();

        do {
            $select = clone $baseSelect;
            $select->where(
                'version_id > ?',
                $hasCustomGrouping ? $fromVersionId : $cursorVersionId
            )->where('version_id <= ?', $lastVersionId);

            if ($hasCustomGrouping) {
                // Retain the full custom aggregation window so grouped payloads stay complete.
                $select->having('MAX(version_id) > ?', $cursorVersionId);
            }
            $select->limit($batchSize);

            $rows = $connection->fetchAll($select);
            if (!$rows) {
                return;
            }

            $ids = [];
            foreach ($rows as $row) {
                $rowCursor = (int)$row[self::CURSOR_COLUMN];
                unset($row[self::CURSOR_COLUMN]);
                $ids[] = count($row) === 1 ? reset($row) : $row;
                $cursorVersionId = max($cursorVersionId, $rowCursor);
            }

            yield [
                'ids' => $ids,
                'version_id' => $cursorVersionId,
            ];

            if ($processId !== getmypid()) {
                return;
            }
        } while ($cursorVersionId < $lastVersionId);
    }
}
