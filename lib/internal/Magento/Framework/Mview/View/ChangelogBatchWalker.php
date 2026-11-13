<?php declare(strict_types=1);
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsContext;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsSelectBuilderInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;

/**
 * Walk changelog entries in bounded, cursor-aware batches.
 */
class ChangelogBatchWalker implements ChangelogBatchWalkerInterface
{
    private const CURSOR_COLUMN = 'max_version_id';

    /** @var ResourceConnection */
    protected ResourceConnection $resourceConnection;

    /** @var IdsSelectBuilderInterface */
    protected IdsSelectBuilderInterface $idsSelectBuilder;

    /** @var ChangelogBatchFactory */
    protected ChangelogBatchFactory $batchFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Generator $generator Retained for constructor compatibility.
     * @param IdsContext $idsContext
     * @param ChangelogBatchFactory|null $batchFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Generator $generator,
        IdsContext $idsContext,
        ?ChangelogBatchFactory $batchFactory = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->idsSelectBuilder = $idsContext->getSelectBuilder();
        $this->batchFactory = $batchFactory ?? ObjectManager::getInstance()->get(ChangelogBatchFactory::class);
    }

    /**
     * Walk through batches with their highest covered changelog version.
     *
     * @param ChangelogInterface $changelog
     * @param int $fromVersionId
     * @param int $lastVersionId
     * @param int $batchSize
     * @return iterable
     */
    public function walk(
        ChangelogInterface $changelog,
        int $fromVersionId,
        int $lastVersionId,
        int $batchSize
    ): iterable {
        $connection = $this->resourceConnection->getConnection();
        $changelogTableName = $this->resourceConnection->getTableName($changelog->getName());

        if (!$connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(\__('Table %1 does not exist', $changelogTableName));
        }

        if ($fromVersionId >= $lastVersionId) {
            return;
        }

        $baseSelect = $this->idsSelectBuilder->build($changelog)
            ->where('version_id <= ?', $lastVersionId);
        $columnName = $changelog->getColumnName();
        $hasCustomGrouping = (bool)$baseSelect->getPart(Select::GROUP);
        if ($hasCustomGrouping) {
            // Grouped payloads need the full aggregation window on every page.
            $baseSelect->where('version_id > ?', $fromVersionId);
        } else {
            $baseSelect->group($columnName);
        }
        $baseSelect->columns([
            self::CURSOR_COLUMN => new Expression('MAX(version_id)'),
        ])
            ->reset(Select::ORDER)
            ->order(self::CURSOR_COLUMN . ' ASC');

        $cursorVersionId = $fromVersionId;
        $processId = \getmypid();

        while ($cursorVersionId < $lastVersionId && !$this->isChildProcess($processId)) {
            $select = clone $baseSelect;
            if ($hasCustomGrouping) {
                $select->having('MAX(version_id) > ?', $cursorVersionId);
            } else {
                $select->where('version_id > ?', $cursorVersionId);
            }
            $select->limit($batchSize);

            $nextBatch = $this->buildBatch($select, $columnName, $hasCustomGrouping);
            if (!$nextBatch) {
                return;
            }
            $cursorVersionId = $nextBatch->getMaxVersionId();

            yield $nextBatch;
        }
    }

    /**
     * Build a cursor-aware batch from the select
     *
     * @param Select $select
     * @param string $columnName
     * @param bool $hasCustomGrouping
     * @return iterable
     */
    public function buildBatch(Select $select, string $columnName, bool $hasCustomGrouping): iterable
    {
        $rows = $this->resourceConnection->getConnection()->fetchAll($select);
        if (!$rows) {
            return [];
        }

        $maxVersionId = (int)$rows[\array_key_last($rows)][self::CURSOR_COLUMN];
        $ids = $hasCustomGrouping
            ? \array_map(
                static fn (array $row): array => \array_diff_key($row, [self::CURSOR_COLUMN => true]),
                $rows
            )
            : \array_column($rows, $columnName);

        return $this->batchFactory->create([
            'data' => [
                'ids' => $ids,
                'max_version_id' => $maxVersionId,
                'can_checkpoint' => !$hasCustomGrouping,
            ],
        ]);
    }

    /**
     * Check if the process was forked.
     *
     * @param int $processID
     * @return bool
     */
    protected function isChildProcess(int $processID): bool
    {
        return $processID !== \getmypid();
    }
}
