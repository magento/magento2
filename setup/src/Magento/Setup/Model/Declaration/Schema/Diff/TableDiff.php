<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\ChangeRegistry;
use Magento\Setup\Model\Declaration\Schema\Comparator;
use Magento\Setup\Model\Declaration\Schema\DiffInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

/**
 * As table can have different types of elements inside itself
 * We need to compare all of this elements
 */
class TableDiff implements DiffInterface
{
    use DiffManager;

    /** Column type for diff */
    const COLUMN_DIFF_TYPE = "columns";

    /** Constraint type for diff */
    const CONSTRAINT_DIFF_TYPE = "constraints";

    /** Constraint type for diff */
    const INDEX_DIFF_TYPE = "indexes";

    /**
     * @var Comparator
     */
    private $comparator;

    /**
     * @param Comparator $comparator
     */
    public function __construct(Comparator $comparator)
    {
        $this->comparator = $comparator;
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
                if ($this->shouldBeCreatedOrRenamed($generatedElements, $element)) {
                    if ($this->shouldBeRenamed($element)) {
                        $generatedElements = $this->registerRename($changeRegistry, $element, $generatedElements);
                    } else {
                        $this->registerCreation($changeRegistry, $element);
                    }
                } else if ($this->shouldBeModified(
                    $this->comparator,
                    $element,
                    $generatedElements[$element->getName()]
                )) {
                    $this->registerModification($changeRegistry, $element, $generatedElements[$element->getName()]);
                }
                //Unset processed elements from generated from db structure
                //All other unprocessed elements will be added as removed ones
                unset($generatedElements[$element->getName()]);
            }

            //Elements that should be removed
            if ($this->shouldBeRemoved($generatedElements)) {
                $this->registerRemoval($changeRegistry, $generatedElements, $declaredElements);
            }
        }
    }
}
