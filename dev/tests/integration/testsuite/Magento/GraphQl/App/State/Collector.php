<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

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
     * @param array $array
     * @return array
     */
    private function cloneArray(array $array) : array
    {
        return array_map(
            function ($element) {
                if (is_object($element)) {
                    return clone $element;
                }
                if (is_array($element)) {
                    return $this->cloneArray($element);
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
                $properties = $this->getPropertiesFromObject($object, true, $didClone);
                $sharedObjects[$serviceName] = [$object, $properties];
            }
        // Note: We have to check again because sometimes cloning objects can indirectly cause adding to Object Manager
        } while ($didClone);
        return $sharedObjects;
    }

    public function getPropertiesFromObject(object $object, $doClone = false, &$didClone = null): array
    {
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
                try {
                    $properties[$propName] = clone $value;
                } catch (\Error $e) {
                    continue;
                }
                $didClone = true;
            } elseif (is_array($value)) {
                $didClone = true;
                $properties[$propName] = $this->cloneArray($value);
            } else {
                $properties[$propName] = $value;
            }
        }
        return $properties;
    }
}
