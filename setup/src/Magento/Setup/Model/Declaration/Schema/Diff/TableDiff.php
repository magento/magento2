<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\ChangeRegistry;
use Magento\Setup\Model\Declaration\Schema\DiffInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

/**
 * As table can have different types of elements inside itself
 * We need to compare all of this elements
 *
 * If element exists only in XML -> then we need to create element
 * If element exists in both version and are different -> then we need to modify element
 * If element exists only in db -> then we need to remove this element
 */
class TableDiff implements DiffInterface
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
     * @param Table | ElementInterface $declaredTable
     * @param Table | ElementInterface $generatedTable
     * @param ChangeRegistry $changeRegistry
     * @inheritdoc
     */
    public function diff(
        ElementInterface $declaredTable,
        ElementInterface $generatedTable,
        ChangeRegistry $changeRegistry
    ) {
        $types = [self::COLUMN_DIFF_TYPE, self::CONSTRAINT_DIFF_TYPE, self::INDEX_DIFF_TYPE];
        //We do inspection for each element type
        foreach ($types as $elementType) {
            $generatedElements = $generatedTable->getElementsByType($elementType);
            $declaredElements = $declaredTable->getElementsByType($elementType);

            foreach ($declaredElements as $element) {
                //If it is new for generated (generated from db) elements - we need to create it
                if ($this->diffManager->shouldBeCreated($generatedElements, $element)) {
                    $this->diffManager->registerCreation($changeRegistry, $element);
                } else if ($this->diffManager->shouldBeModified(
                    $element,
                    $generatedElements[$element->getName()]
                )) {
                    $this->diffManager
                        ->registerModification($changeRegistry, $element, $generatedElements[$element->getName()]);
                }
                //Unset processed elements from generated from db structure
                //All other unprocessed elements will be added as removed ones
                unset($generatedElements[$element->getName()]);
            }

            //Elements that should be removed
            if ($this->diffManager->shouldBeRemoved($generatedElements)) {
                $this->diffManager->registerRemoval($changeRegistry, $generatedElements, $declaredElements);
            }
        }
    }
}
