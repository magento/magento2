<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Integrity\Library;

use Laminas\Code\Reflection\ClassReflection;
use Magento\Framework\GetParameterClassTrait;
use ReflectionException;

/**
 * Provide dependencies for the file
 */
class Injectable
{
    use GetParameterClassTrait;

    /**
     * @var string[]
     */
    protected $dependencies = [];

    /**
     * Get dependencies
     *
     * @param ClassReflection $class
     *
     * @return string[]
     * @throws ReflectionException
     */
    public function getDependencies(ClassReflection $class): array
    {
        foreach ($class->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }

            foreach ($method->getParameters() as $parameter) {
                try {
                    $dependency = $this->getParameterClass($parameter);
                    if ($dependency !== null) {
                        $this->dependencies[] = $dependency->getName();
                    }
                } catch (ReflectionException $e) {
                    if (preg_match('#^Class ([A-Za-z0-9_\"\\\\]+) does not exist$#', $e->getMessage(), $result)) {
                        $this->dependencies[] = trim($result[1], '"');
                    } else {
                        throw $e;
                    }
                }
            }
        }

        return $this->dependencies;
    }
}
