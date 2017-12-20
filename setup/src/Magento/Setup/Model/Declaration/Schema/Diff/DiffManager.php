<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\Comparator;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

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
     * @param ElementInterface[] $generatedElements
     * @param ElementInterface $element
     * @return bool
     */
    public function shouldBeCreated(array $generatedElements, ElementInterface $element)
    {
        return !isset($generatedElements[$element->getName()]);
    }

    /**
     * Check whether we have elements that should be removed from database
     *
     * @param array $generatedElements
     * @return bool
     */
    public function shouldBeRemoved(array $generatedElements)
    {
        return !empty($generatedElements);
    }

    /**
     * Register element, that should changes
     *
     *
     * @param DiffInterface $diff
     * @param ElementInterface $element
     * @param ElementInterface $generatedElement
     */
    public function registerModification(
        DiffInterface $diff,
        ElementInterface $element,
        ElementInterface $generatedElement
    ) {
        $diff->register(
            $element,
            $element->getElementType(),
            DiffInterface::CHANGE_OPERATION,
            $generatedElement
        );
    }

    /**
     * If elements really dont exists in declaration - we will remove them
     * If some mistake happens (and element is just not preprocessed), we will throw exception
     *
     * @param DiffInterface $diff
     * @param ElementInterface[] $generatedElements
     * @param ElementInterface[] $elements
     */
    public function registerRemoval(
        DiffInterface $diff,
        array $generatedElements,
        array $elements
    ) {
        foreach ($generatedElements as $generatedElement) {
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
                $generatedElement->getElementType(),
                DiffInterface::REMOVE_OPERATION
            );
        }
    }

    /**
     * @param DiffInterface $diff
     * @param ElementInterface $element
     */
    public function registerCreation(DiffInterface $diff, ElementInterface $element)
    {
        $diff->register(
            $element,
            $element->getElementType(),
            DiffInterface::CREATE_OPERATION
        );
    }

    /**
     * Check whether element should be modified or not
     *
     * @param ElementInterface $element
     * @param ElementInterface $generatedElement
     * @return bool
     */
    public function shouldBeModified(
        ElementInterface $element,
        ElementInterface $generatedElement
    ) {
        return !$this->comparator->compare($element, $generatedElement);
    }
}
