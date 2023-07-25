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
 * Collects shared objects from ObjectManager and clones properties for later comparison
 */
class Collector
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Recursively clone objects in array.
     *
     * TODO: avoid infinite recursion when $array is cyclic.
     *
     * @param array $array
     * @param bool $callResetState
     * @return array
     */
    private function cloneArray(array $array, bool $callResetState) : array
    {
        return array_map(
            function ($element) use ($callResetState) {
                if (is_object($element)) {
                    if ($callResetState && $element instanceof ResetAfterRequestInterface) {
                        $element->_resetState();
                    }
                    return clone $element;
                }
                if (is_array($element)) {
                    return $this->cloneArray($element, $callResetState);
                }
                return $element;
            },
            $array
        );
    }

    /**
     * Gets shared objects from ObjectManager using reflection and clones properties that are objects
     *
     * @return array
     * @throws \Exception
     */
    public function getSharedObjects(): array
    {
        $sharedObjects = [];
        $obj = new \ReflectionObject($this->objectManager);
        if (!$obj->hasProperty('_sharedInstances')) {
            throw new \Exception('Cannot get shared objects from ' . get_class($this->objectManager));
        }
        do {
            $property = $obj->getProperty('_sharedInstances');
            $property->setAccessible(true);
            $didClone = false;
            foreach ($property->getValue($this->objectManager) as $serviceName => $object) {
                if (array_key_exists($serviceName, $sharedObjects)) {
                    continue;
                }
                if ($object instanceof \Magento\Framework\ObjectManagerInterface) {
                    continue;
                }
                if ($object instanceof ResetAfterRequestInterface) {
                    $object->_resetState();
                }
                $properties = $this->getPropertiesFromObject($object, true, $didClone);
                $sharedObjects[$serviceName] = [$object, $properties];
            }
        // Note: We have to check again because sometimes cloning objects can indirectly cause adding to Object Manager
        } while ($didClone);
        return $sharedObjects;
    }

    /**
     * Gets all the objects' properties as they were originally constructed, and current, as well as object itself
     *
     * @return array
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
            $objects[] = [
                'object' => $object,
                'constructedProperties' => $propertiesBefore,
                'currentProperties' => $this->getPropertiesFromObject($object),
            ];
        }
        return $objects;
    }

    public function getPropertiesFromObject(object $object, $doClone = false, &$didClone = null): array
    {
        $callResetState = true;
        $objReflection = new \ReflectionObject($object);
        $properties = [];
        foreach ($objReflection->getProperties() as $property) {
            $propName = $property->getName();
            $property->setAccessible(true);
            $value = $property->getValue($object);
            if (!$doClone) {
                $properties[$propName] = $value;
                continue;
            }
            if (is_object($value)) {
                $didClone = true;
                if ($callResetState && $value instanceof ResetAfterRequestInterface) {
                    // Calling _resetState helps us avoid adding skip/filter for these classes.
                    $value->_resetState();
                }
                $properties[$propName] = clone $value;
            } elseif (is_array($value)) {
                $didClone = true;
                $properties[$propName] = $this->cloneArray($value, $callResetState);
            } else {
                $properties[$propName] = $value;
            }
        }
        return $properties;
    }
}
