<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\ChangeRegistry;
use Magento\Setup\Model\Declaration\Schema\ChangeRegistryInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Schema;

/**
 * Agregation root of all diffs
 * Loop through all tables and find difference between them
 *
 * If table exists only in XML -> then we need to create table
 * If table exists in both version -> then we need to go deeper and inspect each element
 * If table exists only in db -> then we need to remove this table
 */
class SchemaDiff
{
    /**
     * @var TableDiff
     */
    private $tableDiff;

    /**
     * @var DiffManager
     */
    private $diffManager;

    /**
     * @param DiffManager $diffManager
     * @param TableDiff $tableDiff
     */
    public function __construct(
        DiffManager $diffManager,
        TableDiff $tableDiff
    ) {
        $this->tableDiff = $tableDiff;
        $this->diffManager = $diffManager;
    }

    /**
     * @param Schema $schema
     * @param Schema $generatedSchema
     * @param ChangeRegistry $changeRegistry
     * @return ChangeRegistryInterface
     */
    public function diff(
        Schema $schema,
        Schema $generatedSchema,
        ChangeRegistry $changeRegistry
    ) {
        $generatedTables = $generatedSchema->getTables();

        foreach ($schema->getTables() as $name => $table) {
            if ($this->diffManager->shouldBeCreated($generatedTables, $table)) {
                $this->diffManager->registerCreation($changeRegistry, $table);
            } else {
                $this->tableDiff->diff($table, $generatedTables[$name], $changeRegistry);
            }

            unset($generatedTables[$name]);
        }
        //Removal process
        if ($this->diffManager->shouldBeRemoved($generatedTables)) {
            $this->diffManager->registerRemoval($changeRegistry, $generatedTables, $schema->getTables());
        }

        return $changeRegistry;
    }
}
