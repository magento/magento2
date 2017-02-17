<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Integrity\Library;

use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\FileReflection;
use Zend\Code\Reflection\ParameterReflection;

/**
 */
class Injectable
{
    /**
     * @var \ReflectionException[]
     */
    protected $dependencies = [];

    /**
     * @param FileReflection $fileReflection
     * @return \ReflectionException[]
     * @throws \ReflectionException
     */
    public function getDependencies(FileReflection $fileReflection)
    {
        foreach ($fileReflection->getClasses() as $class) {
            /** @var ClassReflection $class */
            foreach ($class->getMethods() as $method) {
                /** @var \Zend\Code\Reflection\MethodReflection $method */
                if ($method->getDeclaringClass()->getName() != $class->getName()) {
                    continue;
                }

                foreach ($method->getParameters() as $parameter) {
                    try {
                        /** @var ParameterReflection $parameter */
                        $dependency = $parameter->getClass();
                        if ($dependency instanceof ClassReflection) {
                            $this->dependencies[] = $dependency->getName();
                        }
                    } catch (\ReflectionException $e) {
                        if (preg_match('#^Class ([A-Za-z0-9_\\\\]+) does not exist$#', $e->getMessage(), $result)) {
                            $this->dependencies[] = $result[1];
                        } else {
                            throw $e;
                        }
                    }
                }
            }
        }

        return $this->dependencies;
    }
}
