<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsContext;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsFetcherInterface;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsSelectBuilderInterface;
use Magento\Framework\Mview\View\ChangelogBatchWalker\IdsTableBuilderInterface;
use Magento\Framework\Phrase;

/**
 * Interface \Magento\Framework\Mview\View\ChangelogBatchWalkerInterface
 *
 */
class ChangelogBatchWalker implements ChangelogBatchWalkerInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private ResourceConnection $resourceConnection;
    /**
     * @var \Magento\Framework\DB\Query\Generator
     */
    private Generator $generator;
    /**
     * @var \Magento\Framework\Mview\View\ChangelogBatchWalker\IdsTableBuilderInterface
     */
    private IdsTableBuilderInterface $idsTableBuilder;
    /**
     * @var \Magento\Framework\Mview\View\ChangelogBatchWalker\IdsSelectBuilderInterface
     */
    private IdsSelectBuilderInterface $idsSelectBuilder;
    /**
     * @var \Magento\Framework\Mview\View\ChangelogBatchWalker\IdsFetcherInterface
     */
    private IdsFetcherInterface $idsFetcher;

    /**
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Framework\DB\Query\Generator $generator
     * @param \Magento\Framework\Mview\View\ChangelogBatchWalker\IdsContext $idsContext
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Generator          $generator,
        IdsContext         $idsContext
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->generator = $generator;
        $this->idsTableBuilder = $idsContext->getTableBuilder();
        $this->idsSelectBuilder = $idsContext->getSelectBuilder();
        $this->idsFetcher = $idsContext->getFetcher();
    }

    /**
     * @inheritdoc
     */
    public function walk(
        ChangelogInterface $changelog,
        int                $fromVersionId,
        int                $lastVersionId,
        int                $batchSize
    ): iterable {
        $connection = $this->resourceConnection->getConnection();
        $changelogTableName = $this->resourceConnection->getTableName($changelog->getName());

        if (!$connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }

        $idsTable = $this->idsTableBuilder->build($changelog);

        try {
            $connection->createTemporaryTable($idsTable);

            $columns = $this->getIdsColumns($idsTable);

            $select = $this->idsSelectBuilder->build($changelog);
            $select
                ->distinct(true)
                ->where('version_id > ?', $fromVersionId)
                ->where('version_id <= ?', $lastVersionId);

            $connection->query(
                $connection->insertFromSelect(
                    $select,
                    $idsTable->getName(),
                    $columns,
                    AdapterInterface::INSERT_IGNORE
                )
            );

            $select = $connection->select()
                ->from($idsTable->getName());

            $queries = $this->generator->generate(
                IdsTableBuilderInterface::FIELD_ID,
                $select,
                $batchSize
            );

            foreach ($queries as $query) {
                $idsQuery = (clone $query)
                    ->reset(Select::COLUMNS)
                    ->columns($columns);

                $ids = $this->idsFetcher->fetch($idsQuery);

                if (empty($ids)) {
                    continue;
                }

                yield $ids;
            }
        } finally {
            $connection->dropTemporaryTable($idsTable->getName());
        }
    }

    /**
     * Collect columns used as ID of changed entries
     *
     * @param \Magento\Framework\DB\Ddl\Table $table
     * @return array
     */
    private function getIdsColumns(Table $table): array
    {
        return array_values(
            array_map(
                static function (array $column) {
                    return $column['COLUMN_NAME'];
                },
                array_filter(
                    $table->getColumns(),
                    static function (array $column) {
                        return $column['PRIMARY'] === false;
                    }
                )
            )
        );
    }
}
