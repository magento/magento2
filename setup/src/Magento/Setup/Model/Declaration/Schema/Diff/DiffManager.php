<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\ChangeRegistryInterface;
use Magento\Setup\Model\Declaration\Schema\Comparator;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementRenamedInterface;

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
     * Check whether this is element is new or not, by checking it in db structure
     *
     * @param ElementInterface[] $generatedElements
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
     * @param ChangeRegistryInterface $changeRegistry
     * @param ElementInterface $element
     * @param ElementInterface $generatedElement
     */
    public function registerModification(
        ChangeRegistryInterface $changeRegistry,
        ElementInterface $element,
        ElementInterface $generatedElement
    ) {
        $changeRegistry->register(
            $element,
            $element->getElementType(),
            ChangeRegistryInterface::CHANGE_OPERATION,
            $generatedElement
        );
    }

    /**
     * If elements really dont exists in declaration - we will remove them
     * If some mistake happens (and element is just not preprocessed), we will throw exception
     *
     * @param ChangeRegistryInterface $changeRegistry
     * @param ElementInterface[] $generatedElements
     * @param ElementInterface[] $elements
     */
    public function registerRemoval(
        ChangeRegistryInterface $changeRegistry,
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

            $changeRegistry->register(
                $generatedElement,
                $generatedElement->getElementType(),
                ChangeRegistryInterface::REMOVE_OPERATION
            );
        }
    }

    /**
     * @param ChangeRegistryInterface $changeRegistry
     * @param ElementInterface $element
     */
    public function registerCreation(ChangeRegistryInterface $changeRegistry, ElementInterface $element)
    {
        $changeRegistry->register(
            $element,
            $element->getElementType(),
            ChangeRegistryInterface::CREATE_OPERATION
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
