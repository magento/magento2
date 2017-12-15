<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\ChangeRegistry;
use Magento\Setup\Model\Declaration\Schema\Dto\Structure;

/**
 * Agregation root of all diffs
 * Loop through all tables and find difference between them
 *
 * If table exists only in XML -> then we need to create table
 * If table exists in both version -> then we need to go deeper and inspect each element
 * If table exists only in db -> then we need to remove this table
 * If table wasRenamedFrom attribute is differ with name -> then we need to rename table
 */
class StructureDiff
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
     * @param Structure $structure
     * @param Structure $generatedStructure
     * @param ChangeRegistry $changeRegistry
     */
    public function diff(
        Structure $structure,
        Structure $generatedStructure,
        ChangeRegistry $changeRegistry
    ) {
        $generatedTables = $generatedStructure->getTables();

        foreach ($structure->getTables() as $name => $table) {
            if ($this->diffManager->shouldBeCreatedOrRenamed($generatedTables, $table)) {
                if ($this->diffManager->shouldBeRenamed($table)) {
                    $generatedTables = $this->diffManager->registerRename($changeRegistry, $table, $generatedTables);
                } else {
                    $this->diffManager->registerCreation($changeRegistry, $table);
                }
            } else {
                $this->tableDiff->diff($table, $generatedTables[$name], $changeRegistry);
            }

            unset($generatedTables[$name]);
        }
        //Removal process
        if ($this->diffManager->shouldBeRemoved($generatedTables)) {
            $this->diffManager->registerRemoval($changeRegistry, $generatedTables, $structure->getTables());
        }
    }
}
