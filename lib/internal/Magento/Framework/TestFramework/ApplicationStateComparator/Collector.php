<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\ApplicationStateComparator;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\ApplicationStateComparator\ObjectManagerInterface as StateObjectManagerInterface;
use WeakReference;

/**
 * Collects shared objects from ObjectManager and copies properties for later comparison
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collector
{
    //phpcs:ignore
    private readonly array $skipListFromConstructed;

    //phpcs:ignore
    private readonly array $skipListBetweenRequests;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param SkipListAndFilterList $skipListAndFilterList
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        SkipListAndFilterList $skipListAndFilterList
    ) {
        $this->skipListFromConstructed =
            $skipListAndFilterList->getSkipList('', CompareType::COMPARE_CONSTRUCTED_AGAINST_CURRENT);
        $this->skipListBetweenRequests = $skipListAndFilterList->getSkipList('', CompareType::COMPARE_BETWEEN_REQUESTS);
    }

    /**
     * Recursively copy objects in array.
     *
     * @param array $array
     * @param CompareType $compareType
     * @param int $recursionLevel
     * @param int $arrayRecursionLevel
     * @return array
     */
    private function copyArray(
        array $array,
        string $compareType,
        int $recursionLevel,
        int $arrayRecursionLevel = 100
    ) : array {
        return array_map(
            function ($element) use (
                $compareType,
                $recursionLevel,
                $arrayRecursionLevel,
            ) {
                if (is_object($element)) {
                    return $this->getPropertiesFromObject(
                        $element,
                        $compareType,
                        $recursionLevel - 1,
                    );
                }
                if (is_array($element)) {
                    if ($arrayRecursionLevel) {
                        return $this->copyArray(
                            $element,
                            $compareType,
                            $recursionLevel,
                            $arrayRecursionLevel - 1,
                        );
                    } else {
                        return '(end of array recursion level)';
                    }
                }
                return $element;
            },
            $array
        );
    }

    /**
     * Gets shared objects from ObjectManager using reflection and copies properties that are objects
     *
     * @param string $shouldResetState
     * @return CollectedObject[]
     * @throws \Exception
     */
    public function getSharedObjects(string $shouldResetState): array
    {
        if ($this->objectManager instanceof StateObjectManagerInterface) {
            $sharedInstances = $this->objectManager->getSharedInstances();
        } else {
            $obj = new \ReflectionObject($this->objectManager);
            if (!$obj->hasProperty('_sharedInstances')) {
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception('Cannot get shared objects from ' . get_class($this->objectManager));
            }
            $property = $obj->getProperty('_sharedInstances');
            $property->setAccessible(true);
            $sharedInstances = $property->getValue($this->objectManager);
        }
        $sharedObjects = [];
        foreach ($sharedInstances as $serviceName => $object) {
            if (array_key_exists($serviceName, $sharedObjects)) {
                continue;
            }
            if (ShouldResetState::DO_RESET_STATE == $shouldResetState &&
                ($object instanceof ResetAfterRequestInterface)) {
                $object->_resetState();
            }
            if ($object instanceof \Magento\Framework\ObjectManagerInterface) {
                continue;
            }
            $sharedObjects[$serviceName] =
                $this->getPropertiesFromObject($object, CompareType::COMPARE_BETWEEN_REQUESTS);
        }
        return $sharedObjects;
    }

    /**
     * Gets all the objects' properties as they were originally constructed, and current, as well as object itself
     *
     * This also calls _resetState on any ResetAfterRequestInterface
     *
     * @return CollectedObjectConstructedAndCurrent[]
     * @throws \Exception
     */
    public function getPropertiesConstructedAndCurrent(): array
    {
        /** @var ObjectManager $objectManager */
        $objectManager = $this->objectManager;
        if (!($objectManager instanceof StateObjectManagerInterface)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Not the correct type of ObjectManager");
        }
        // Calling _resetState helps us avoid adding skip/filter for these classes.
        $objectManager->_resetState();
        $objects = [];
        $weakReferenceList = [];
        foreach ($objectManager->getResetter()->getCollectedWeakMap() as $object => $propertiesBefore) {
            $weakReferenceList[] = WeakReference::create($object);
        }
        foreach ($weakReferenceList as $weakReference) {
            $object = $weakReference->get();
            if (!$object) {
                continue;
            }
            $propertiesBefore = $objectManager->getResetter()->getCollectedWeakMap()[$object];
            $objects[] = new CollectedObjectConstructedAndCurrent(
                WeakReference::create($object),
                $propertiesBefore,
                $this->getPropertiesFromObject($object, CompareType::COMPARE_CONSTRUCTED_AGAINST_CURRENT),
            );
        }
        return $objects;
    }

    /**
     * Gets properties from object and returns CollectedObject
     *
     * @param object $object
     * @param CompareType $compareType
     * @param int $recursionLevel
     * @return CollectedObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getPropertiesFromObject(
        object $object,
        string $compareType,
        int $recursionLevel = 0,
    ): CollectedObject {
        $className = get_class($object);
        $skipList = $compareType == CompareType::COMPARE_BETWEEN_REQUESTS ?
            $this->skipListBetweenRequests : $this->skipListFromConstructed ;
        if (array_key_exists($className, $skipList)) {
            return CollectedObject::getSkippedObject();
        }
        if ($this->objectManager instanceof StateObjectManagerInterface) {
            $serviceName = array_search($object, $this->objectManager->getSharedInstances(), true);
            if ($serviceName && array_key_exists($serviceName, $skipList)) {
                return CollectedObject::getSkippedObject();
            }
        }
        if ($recursionLevel < 0) {
            /* Note: When we reach end of recursionLevel, skip getting properties, but still get the name, object id,
             * and WeakReference, so that we can compare if they have changed. */
            return new CollectedObject(
                $className,
                [],
                spl_object_id($object),
                WeakReference::create($object),
            );
        }
        $objReflection = new \ReflectionObject($object);
        $properties = [];
        foreach ($objReflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $property->setAccessible(true);
            if (!$property->isInitialized($object)) {
                continue;
            }
            $value = $property->getValue($object);
            if (is_object($value)) {
                $properties[$propertyName] = $this->getPropertiesFromObject(
                    $value,
                    $compareType,
                    $recursionLevel - 1,
                );
            } elseif (is_array($value)) {
                $properties[$propertyName] = $this->copyArray(
                    $value,
                    $compareType,
                    $recursionLevel,
                );
            } else {
                $properties[$propertyName] = $value;
            }
        }
        return new CollectedObject(
            $className,
            $properties,
            spl_object_id($object),
            WeakReference::create($object),
        );
    }
}
