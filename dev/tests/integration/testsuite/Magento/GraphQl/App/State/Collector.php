<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Collects shared objects from ObjectManager and copies properties for later comparison
 */
class Collector
{
    private array $skipListFromConstructed;
    private array $skipListBetweenRequests;

    public function __construct(
        private ObjectManagerInterface $objectManager,
        SkipListAndFilterList $skipListAndFilterList
    ) {
        $this->skipListFromConstructed =
            $skipListAndFilterList->getSkipList('', CompareType::CompareConstructedAgainstCurrent);
        $this->skipListBetweenRequests = $skipListAndFilterList->getSkipList('', CompareType::CompareBetweenRequests);
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
        CompareType $compareType,
        int $recursionLevel,
        int $arrayRecursionLevel = 100
    ) : array
    {
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
     * Gets shared objects from ObjectManager using reflection and clones properties that are objects
     *
     * @param ShouldResetState $shouldResetState
     * @return CollectedObject[]
     */
    public function getSharedObjects(ShouldResetState $shouldResetState): array
    {
        $sharedObjects = [];
        $obj = new \ReflectionObject($this->objectManager);
        if (!$obj->hasProperty('_sharedInstances')) {
            throw new \Exception('Cannot get shared objects from ' . get_class($this->objectManager));
        }
        $property = $obj->getProperty('_sharedInstances');
        $property->setAccessible(true);
        foreach ($property->getValue($this->objectManager) as $serviceName => $object) {
            if (array_key_exists($serviceName, $sharedObjects)) {
                continue;
            }
            if (ShouldResetState::DoResetState == $shouldResetState &&
                ($object instanceof ResetAfterRequestInterface)) {
                $object->_resetState();
            }
            if ($object instanceof \Magento\Framework\ObjectManagerInterface) {
                continue;
            }
            $sharedObjects[$serviceName] =
                $this->getPropertiesFromObject($object, CompareType::CompareBetweenRequests);
        }
        return $sharedObjects;
    }

    /**
     * Gets all the objects' properties as they were originally constructed, and current, as well as object itself
     *
     * This also calls _resetState on any ResetAfterRequestInterface
     *
     * @return CollectedObjectConstructedAndCurrent[]
     */
    public function getPropertiesConstructedAndCurrent(): array
    {
        /** @var ObjectManager $objectManager */
        $objectManager = $this->objectManager;
        if (!($objectManager instanceof ObjectManager)) {
            throw new \Exception("Not the correct type of ObjectManager");
        }
        // Calling _resetState helps us avoid adding skip/filter for these classes.
        foreach ($objectManager->getWeakMap() as $object => $propertiesBefore) {
            if ($object instanceof ResetAfterRequestInterface) {
                $object->_resetState();
            }
            unset($object);
        }
        /* Note: We must force garbage collection to clean up cyclic referenced objects after _resetState()
        Otherwise, they may still show up in the WeakMap. */
        $collectedCount = gc_collect_cycles();
        $objects = [];
        foreach ($objectManager->getWeakMap() as $object => $propertiesBefore) {
            $objects[] = new CollectedObjectConstructedAndCurrent(
                $object,
                $propertiesBefore,
                $this->getPropertiesFromObject($object, CompareType::CompareConstructedAgainstCurrent),
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
     */
    public function getPropertiesFromObject(
        object $object,
        CompareType $compareType,
        int $recursionLevel = 1,
    ): CollectedObject {
        if ($recursionLevel < 0) {
            return CollectedObject::getRecursionEndObject();
        }
        $className = get_class($object);
        $skipList = $compareType == CompareType::CompareBetweenRequests ?
            $this->skipListBetweenRequests : $this->skipListFromConstructed ;
        if (array_key_exists($className, $skipList)) {
            return CollectedObject::getSkippedObject();
        }
        $objReflection = new \ReflectionObject($object);
        $properties = [];
        foreach ($objReflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $property->setAccessible(true);
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
        );
    }
}
