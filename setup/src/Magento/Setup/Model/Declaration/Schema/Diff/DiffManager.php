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
use Magento\Setup\Model\Declaration\Schema\Dto\Structure;

/**
 * Helper which provide methods, that helps to compare 2 different nodes:
 * For instance, 2 columns between each other
 */
trait DiffManager
{
    /**
     * Check whether new name is not corresponds with olds one
     *
     * @param ElementRenamedInterface | ElementInterface $element
     * @return mixed
     */
    public function shouldBeRenamed(ElementInterface $element)
    {
        return $element->getName() !== $element->wasRenamedFrom() && $element->wasRenamedFrom() !== null;
    }

    /**
     * Check whether this is element is new or not, by checking it in db structure
     *
     * @param ElementInterface[] $generatedElements
     * @param ElementInterface | ElementRenamedInterface $element
     * @return bool
     */
    public function shouldBeCreatedOrRenamed(array $generatedElements, ElementInterface $element)
    {
        return
            !isset($generatedElements[$element->getName()]) ||
            $this->shouldBeRenamed($element) && !isset($generatedElements[$element->wasRenamedFrom()]);
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
     * After registration rename, we need to remove renamed element from generated structure
     * in order to prevent double registration of this object
     *
     * @param ChangeRegistryInterface $changeRegistry
     * @param ElementInterface | ElementRenamedInterface $element
     * @param array $generatedElements
     * @return array
     */
    public function registerRename(
        ChangeRegistryInterface $changeRegistry,
        ElementInterface $element,
        array $generatedElements
    ) {
        $changeRegistry->register(
            $element,
            $element->getElementType(),
            ChangeRegistryInterface::RENAME_OPERAION,
            $generatedElements[$element->wasRenamedFrom()]
        );

        unset($generatedElements[$element->wasRenamedFrom()]);
        return $generatedElements;
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
     * @param Comparator $comparator
     * @param ElementInterface $element
     * @param ElementInterface $generatedElement
     * @return bool
     */
    public function shouldBeModified(
        Comparator $comparator,
        ElementInterface $element,
        ElementInterface $generatedElement
    ) {
        return !$comparator->compare($element, $generatedElement);
    }
}
