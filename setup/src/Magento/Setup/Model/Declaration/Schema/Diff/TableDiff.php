<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\Operations\AddComplexElement;
use Magento\Setup\Model\Declaration\Schema\Operations\DropElement;
use Magento\Setup\Model\Declaration\Schema\Operations\ModifyColumn;
use Magento\Setup\Model\Declaration\Schema\Operations\ModifyElement;
use Magento\Setup\Model\Declaration\Schema\Operations\ReCreateTable;

/**
 * As table can have different types of elements inside itself
 * We need to compare all of this elements
 *
 * If element exists only in XML -> then we need to create element
 * If element exists in both version and are different -> then we need to modify element
 * If element exists only in db -> then we need to remove this element
 */
class TableDiff
{
    /**
     * Column type for diff
     */
    const COLUMN_DIFF_TYPE = "columns";

    /**
     * Constraint type for diff
     */
    const CONSTRAINT_DIFF_TYPE = "constraints";

    /**
     * Constraint type for diff
     */
    const INDEX_DIFF_TYPE = "indexes";

    /**
     * @var DiffManager
     */
    private $diffManager;

    /**
     * @param DiffManager $diffManager
     */
    public function __construct(DiffManager $diffManager)
    {
        $this->diffManager = $diffManager;
    }

    /**
     * As SQL engine can automatically create indexes for foreign keys in order to speedup selection
     * and we do not have this keys in declaration - we need to ignore them
     *
     * @param  Table   $table
     * @param  Index[] $indexes
     * @return Index[]
     */
    private function excludeAutoIndexes(Table $table, array $indexes)
    {
        foreach ($table->getReferenceConstraints() as $constraint) {
            unset($indexes[$constraint->getName()]);
        }

        return $indexes;
    }

    /**
     * As foreign key is constraint, that do not allow to change column schema definition
     * we need to disable it in order to change column definition. When column definition
     * will be changed we need to enable foreign key again
     * We need to start column modification from parent table (reference table) and then go to
     * tables that have foreign keys
     *
     * @param Table $declaredTable
     * @param Table $generatedTable
     * @param Diff $diff
     * @return Diff
     */
    private function turnOffForeignKeys(Table $declaredTable, Table $generatedTable, Diff $diff)
    {
        $changes = $diff->getChange($generatedTable->getName(), ModifyColumn::OPERATION_NAME);

        foreach ($changes as $elementHistory) {
            /** If this is column we need to recreate foreign key */
            if ($elementHistory->getNew() instanceof Column) {
                $column = $elementHistory->getNew();
                $references = $generatedTable->getReferenceConstraints();
                $declaredReferences = $declaredTable->getReferenceConstraints();

                foreach ($references as $reference) {
                    /** In case when we have foreign key on column, that should be modified */
                    if ($reference->getColumn()->getName() === $column->getName() &&
                        isset($declaredReferences[$reference->getName()])
                    ) {
                        /**
                         * Lets disable foreign key and enable it again
                         * As between drop and create operations we have operation of modification
                         * we will drop key, modify column, add key
                         */
                        $diff = $this->diffManager->registerReferenceDrop($reference, $diff);
                        $diff->register($reference, AddComplexElement::OPERATION_NAME);
                    }
                }
            }
        }

        return $diff;
    }

    /**
     * @param Table | ElementInterface $declaredTable
     * @param Table | ElementInterface $generatedTable
     * @param Diff                     $diff
     * @inheritdoc
     */
    public function diff(
        ElementInterface $declaredTable,
        ElementInterface $generatedTable,
        Diff $diff
    ) {
        //Handle changing shard
        if ($this->diffManager->shouldBeModified($declaredTable, $generatedTable)) {
            $diff->register(
                $declaredTable,
                ReCreateTable::OPERATION_NAME,
                $generatedTable
            );
        }

        $types = [self::COLUMN_DIFF_TYPE, self::CONSTRAINT_DIFF_TYPE, self::INDEX_DIFF_TYPE];
        //We do inspection for each element type
        foreach ($types as $elementType) {
            $generatedElements = $generatedTable->getElementsByType($elementType);
            $declaredElements = $declaredTable->getElementsByType($elementType);

            if ($elementType === self::INDEX_DIFF_TYPE) {
                $generatedElements = $this->excludeAutoIndexes($generatedTable, $generatedElements);
                $declaredElements = $this->excludeAutoIndexes($declaredTable, $declaredElements);
            }

            foreach ($declaredElements as $elementName => $element) {
                //If it is new for generated (generated from db) elements - we need to create it
                if (!isset($generatedElements[$elementName])) {
                    $diff = $this->diffManager->registerCreation($diff, $element);
                } elseif ($this->diffManager->shouldBeModified(
                    $element,
                    $generatedElements[$elementName]
                )
                ) {
                    $diff = $this->diffManager
                        ->registerModification($diff, $element, $generatedElements[$elementName]);
                }
                //Unset processed elements from generated from db schema
                //All other unprocessed elements will be added as removed ones
                unset($generatedElements[$elementName]);
            }

            //Elements that should be removed
            if ($this->diffManager->shouldBeRemoved($generatedElements)) {
                $diff = $this->diffManager->registerRemoval($diff, $generatedElements, $declaredElements);
            }
        }

        $diff = $this->turnOffForeignKeys($declaredTable, $generatedTable, $diff);
        return $diff;
    }
}
