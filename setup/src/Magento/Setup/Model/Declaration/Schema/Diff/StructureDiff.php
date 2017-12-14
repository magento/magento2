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
 * Loop through all tables
 */
class StructureDiff
{
    use DiffManager;

    /**
     * @var TableDiff
     */
    private $tableDiff;

    /**
     * StructureDiff constructor.
     * @param TableDiff $tableDiff
     */
    public function __construct(TableDiff $tableDiff)
    {
        $this->tableDiff = $tableDiff;
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
            if ($this->shouldBeCreatedOrRenamed($generatedTables, $table)) {
                if ($this->shouldBeRenamed($table)) {
                    $generatedTables = $this->registerRename($changeRegistry, $table, $generatedTables);
                } else {
                    $this->registerCreation($changeRegistry, $table);
                }
            } else {
                $this->tableDiff->diff($table, $generatedTables[$name], $changeRegistry);
            }

            unset($generatedTables[$name]);
        }
        //Removal process
        if ($this->shouldBeRemoved($generatedTables)) {
            $this->registerRemoval($changeRegistry, $generatedTables, $structure->getTables());
        }
    }
}
