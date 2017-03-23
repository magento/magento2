<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Zend\Code\Reflection\ClassReflection;

class NameFinder
{
    /**
     * Convert Data Object getter name into field name.
     *
     * @param string $getterName
     * @return string
     */
    public function getFieldNameFromGetterName($getterName)
    {
        if ((strpos($getterName, 'get') === 0)) {
            /** Remove 'get' prefix and make the first letter lower case */
            $fieldName = substr($getterName, strlen('get'));
        } elseif ((strpos($getterName, 'is') === 0)) {
            /** Remove 'is' prefix and make the first letter lower case */
            $fieldName = substr($getterName, strlen('is'));
        } elseif ((strpos($getterName, 'has') === 0)) {
            /** Remove 'has' prefix and make the first letter lower case */
            $fieldName = substr($getterName, strlen('has'));
        } else {
            $fieldName = $getterName;
        }
        return lcfirst($fieldName);
    }

    /**
     * Convert Data Object getter short description into field description.
     *
     * @param string $shortDescription
     * @return string
     */
    public function getFieldDescriptionFromGetterDescription($shortDescription)
    {
        return ucfirst(substr(strstr($shortDescription, " "), 1));
    }
    
    /**
     * Find the getter method name for a property from the given class
     *
     * @param ClassReflection $class
     * @param string $camelCaseProperty
     * @return string processed method name
     * @throws \LogicException If $camelCaseProperty has no corresponding getter method
     */
    public function getGetterMethodName(ClassReflection $class, $camelCaseProperty)
    {
        $getterName = 'get' . $camelCaseProperty;
        $boolGetterName = 'is' . $camelCaseProperty;
        return $this->findAccessorMethodName($class, $camelCaseProperty, $getterName, $boolGetterName);
    }

    /**
     * Find the setter method name for a property from the given class
     *
     * @param ClassReflection $class
     * @param string $camelCaseProperty
     * @return string processed method name
     * @throws \LogicException If $camelCaseProperty has no corresponding setter method
     */
    public function getSetterMethodName(ClassReflection $class, $camelCaseProperty)
    {
        $setterName = 'set' . $camelCaseProperty;
        $boolSetterName = 'setIs' . $camelCaseProperty;
        return $this->findAccessorMethodName($class, $camelCaseProperty, $setterName, $boolSetterName);
    }

    /**
     * Find the accessor method name for a property from the given class
     *
     * @param ClassReflection $class
     * @param string $camelCaseProperty
     * @param string $accessorName
     * @param bool $boolAccessorName
     * @return string processed method name
     * @throws \LogicException If $camelCaseProperty has no corresponding setter method
     */
    public function findAccessorMethodName(
        ClassReflection $class,
        $camelCaseProperty,
        $accessorName,
        $boolAccessorName
    ) {
        if ($this->hasMethod($class, $accessorName)) {
            $methodName = $accessorName;
            return $methodName;
        } elseif ($this->hasMethod($class, $boolAccessorName)) {
            $methodName = $boolAccessorName;
            return $methodName;
        } else {
            throw new \LogicException(
                sprintf(
                    'Property "%s" does not have corresponding setter in class "%s".',
                    $camelCaseProperty,
                    $class->getName()
                )
            );
        }
    }

    /**
     * Checks if method is defined
     *
     * Case sensitivity of the method is taken into account.
     *
     * @param ClassReflection $class
     * @param string $methodName
     * @return bool
     */
    public function hasMethod(ClassReflection $class, $methodName)
    {
        return $class->hasMethod($methodName) && ($class->getMethod($methodName)->getName() == $methodName);
    }
}
