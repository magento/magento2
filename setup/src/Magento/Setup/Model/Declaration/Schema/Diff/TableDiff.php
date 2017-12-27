<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
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
        //Handle changing resource
        if ($generatedTable->getResource() !== $declaredTable->getResource()) {
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
            }

            foreach ($declaredElements as $element) {
                //If it is new for generated (generated from db) elements - we need to create it
                if ($this->diffManager->shouldBeCreated($generatedElements, $element)) {
                    $diff = $this->diffManager->registerCreation($diff, $element);
                } elseif ($this->diffManager->shouldBeModified(
                    $element,
                    $generatedElements[$element->getName()]
                )
                ) {
                    $diff = $this->diffManager
                        ->registerModification($diff, $element, $generatedElements[$element->getName()]);
                }
                //Unset processed elements from generated from db schema
                //All other unprocessed elements will be added as removed ones
                unset($generatedElements[$element->getName()]);
            }

            //Elements that should be removed
            if ($this->diffManager->shouldBeRemoved($generatedElements)) {
                $diff = $this->diffManager->registerRemoval($diff, $generatedElements, $declaredElements);
            }
        }

        return $diff;
    }
}
