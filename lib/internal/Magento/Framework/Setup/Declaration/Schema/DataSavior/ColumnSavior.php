<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\DataSavior;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

/**
 * Allows to dump and restore data for one specific field
 */
class ColumnSavior implements DataSaviorInterface
{
    /**
     * @var SelectGeneratorFactory
     */
    private $selectGeneratorFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var UniqueConstraintsResolver
     */
    private $uniqueConstraintsResolver;

    /**
     * @var DumpAccessorInterface
     */
    private $dumpAccessor;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    /**
     * TableDump constructor.
     * @param ResourceConnection $resourceConnection
     * @param SelectGeneratorFactory $selectGeneratorFactory
     * @param DumpAccessorInterface $dumpAccessor
     * @param UniqueConstraintsResolver $uniqueConstraintsResolver
     * @param SelectFactory $selectFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SelectGeneratorFactory $selectGeneratorFactory,
        DumpAccessorInterface $dumpAccessor,
        UniqueConstraintsResolver $uniqueConstraintsResolver,
        SelectFactory $selectFactory
    ) {
        $this->selectGeneratorFactory = $selectGeneratorFactory;
        $this->resourceConnection = $resourceConnection;
        $this->uniqueConstraintsResolver = $uniqueConstraintsResolver;
        $this->dumpAccessor = $dumpAccessor;
        $this->selectFactory = $selectFactory;
    }

    /**
     * Prepare select to database
     *
     * @param Column $column
     * @param array $fieldsToDump
     * @return \Magento\Framework\DB\Select
     */
    private function prepareColumnSelect(Column $column, array $fieldsToDump)
    {
        $adapter = $this->resourceConnection->getConnection($column->getTable()->getResource());
        $select = $this->selectFactory->create($adapter);
        $select->from($column->getTable()->getName(), $fieldsToDump);
        return $select;
    }

    /**
     * @inheritdoc
     * @param Column | ElementInterface $column
     * @return void
     */
    public function dump(ElementInterface $column)
    {
        $columns = $this->uniqueConstraintsResolver->resolve($column->getTable());

        /**
         * Only if table have unique keys or primary key
         */
        if ($columns) {
            $connectionName = $column->getTable()->getResource();
            $columns[] = $column->getName();
            $select = $this->prepareColumnSelect($column, $columns);
            $selectGenerator = $this->selectGeneratorFactory->create();
            $resourceSignature = $this->generateDumpFileSignature($column);

            foreach ($selectGenerator->generator($select, $connectionName) as $data) {
                $this->dumpAccessor->save($resourceSignature, $data);
            }
        }
    }

    /**
     * Do Insert on duplicate to table, where field should be restored
     *
     * @param Table $table
     * @param array $data
     */
    private function applyDumpChunk(Table $table, $data)
    {
        $columns = [];
        $adapter = $this->resourceConnection->getConnection($table->getResource());
        $firstRow = reset($data);

        /**
         * Prepare all table fields
         */
        foreach ($table->getColumns() as $column) {
            $columns[$column->getName()] = $column->getName();
        }

        $adapter->insertOnDuplicate($table->getName(), $data, array_keys($firstRow));
    }

    /**
     * @param Column | ElementInterface $column
     * @return string
     */
    private function generateDumpFileSignature(Column $column)
    {
        $dimensions = [
            $column->getTable()->getName(),
            $column->getElementType(),
            $column->getName()
        ];

        return implode("_", $dimensions);
    }

    /**
     * @param Column | ElementInterface $column
     * @inheritdoc
     */
    public function restore(ElementInterface $column)
    {
        $file = $this->generateDumpFileSignature($column);
        $generator = $this->dumpAccessor->read($file);

        while ($generator->valid()) {
            $data = $generator->current();
            $this->applyDumpChunk(
                $column->getTable(),
                $data
            );
            $generator->next();
        }

        $this->dumpAccessor->destruct($file);
    }

    /**
     * @param ElementInterface $element
     * @return bool
     */
    public function isAcceptable(ElementInterface $element)
    {
        return $element instanceof Column;
    }
}
