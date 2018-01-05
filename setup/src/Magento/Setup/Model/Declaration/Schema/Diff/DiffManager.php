<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\Comparator;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\Operations\AddColumn;
use Magento\Setup\Model\Declaration\Schema\Operations\AddComplexElement;
use Magento\Setup\Model\Declaration\Schema\Operations\CreateTable;
use Magento\Setup\Model\Declaration\Schema\Operations\DropElement;
use Magento\Setup\Model\Declaration\Schema\Operations\DropTable;
use Magento\Setup\Model\Declaration\Schema\Operations\ModifyElement;

/**
 * Helper which provide methods, that helps to compare 2 different nodes:
 * For instance, 2 columns between each other
 */
class DiffManager
{
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
     * Check whether this is element is new or not, by checking it in db schema
     *
     * @param  ElementInterface[] $generatedElements
     * @param  ElementInterface   $element
     * @return bool
     */
    public function shouldBeCreated(array $generatedElements, ElementInterface $element)
    {
        return !isset($generatedElements[$element->getName()]);
    }

    /**
     * Check whether we have elements that should be removed from database
     *
     * @param  array $generatedElements
     * @return bool
     */
    public function shouldBeRemoved(array $generatedElements)
    {
        return !empty($generatedElements);
    }

    /**
     * Register element, that should changes
     *
     * @param  DiffInterface    $diff
     * @param  ElementInterface $element
     * @param  ElementInterface $generatedElement
     * @return DiffInterface
     */
    public function registerModification(
        DiffInterface $diff,
        ElementInterface $element,
        ElementInterface $generatedElement
    ) {
        $diff->register(
            $element,
            ModifyElement::OPERATION_NAME,
            $generatedElement
        );
        return $diff;
    }

    /**
     * If elements really dont exists in declaration - we will remove them
     * If some mistake happens (and element is just not preprocessed), we will throw exception
     *
     * @param  DiffInterface      $diff
     * @param  ElementInterface[] $generatedElements
     * @param  ElementInterface[] $elements
     * @return DiffInterface
     */
    public function registerRemoval(
        DiffInterface $diff,
        array $generatedElements,
        array $elements
    ) {
        foreach ($generatedElements as $generatedElement) {
            $operation = $generatedElement instanceof Table ? DropTable::OPERATION_NAME : DropElement::OPERATION_NAME;
            if (isset($elements[$generatedElement->getName()])) {
                throw new \LogicException(
                    sprintf(
                        "Cannot find difference for element with name %s",
                        $generatedElement->getName()
                    )
                );
            }

            $diff->register(
                $generatedElement,
                $operation,
                $generatedElement
            );
        }

        return $diff;
    }

    /**
     * @param DiffInterface    $diff
     * @param ElementInterface $element
     * @return DiffInterface
     */
    public function registerCreation(DiffInterface $diff, ElementInterface $element)
    {
        if ($element instanceof Table) {
            $operation = CreateTable::OPERATION_NAME;
        } elseif ($element instanceof Column) {
            $operation = AddColumn::OPERATION_NAME;
        } else {
            $operation = AddComplexElement::OPERATION_NAME;
        }

        $diff->register(
            $element,
            $operation
        );

        return $diff;
    }

    /**
     * Check whether element should be modified or not
     *
     * @param  ElementInterface $element
     * @param  ElementInterface $generatedElement
     * @return bool
     */
    public function shouldBeModified(
        ElementInterface $element,
        ElementInterface $generatedElement
    ) {
        return !$this->comparator->compare($element, $generatedElement);
    }
}
