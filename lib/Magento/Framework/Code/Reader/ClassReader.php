<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Code\Reader;

class ClassReader
{
    /**
     * Read class constructor signature
     *
     * @param string $className
     * @return array|null
     * @throws \ReflectionException
     */
    public function getConstructor($className)
    {
        $class = new \ReflectionClass($className);
        $result = null;
        $constructor = $class->getConstructor();
        if ($constructor) {
            $result = array();
            /** @var $parameter \ReflectionParameter */
            foreach ($constructor->getParameters() as $parameter) {
                try {
                    $result[] = array(
                        $parameter->getName(),
                        $parameter->getClass() !== null ? $parameter->getClass()->getName() : null,
                        !$parameter->isOptional(),
                        $parameter->isOptional() ? $parameter
                            ->isDefaultValueAvailable() ? $parameter
                            ->getDefaultValue() : null : null
                    );
                } catch (\ReflectionException $e) {
                    $message = $e->getMessage();
                    throw new \ReflectionException($message, 0, $e);
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve parent relation information for type in a following format
     * array(
     *     'Parent_Class_Name',
     *     'Interface_1',
     *     'Interface_2',
     *     ...
     * )
     *
     * @param string $className
     * @return string[]
     */
    public function getParents($className)
    {
        $parentClass = get_parent_class($className);
        if ($parentClass) {
            $result = array();
            $interfaces = class_implements($className);
            if ($interfaces) {
                $parentInterfaces = class_implements($parentClass);
                if ($parentInterfaces) {
                    $result = array_values(array_diff($interfaces, $parentInterfaces));
                } else {
                    $result = array_values($interfaces);
                }
            }
            array_unshift($result, $parentClass);
        } else {
            $result = array_values(class_implements($className));
            if ($result) {
                array_unshift($result, null);
            } else {
                $result = array();
            }
        }
        return $result;
    }
}
