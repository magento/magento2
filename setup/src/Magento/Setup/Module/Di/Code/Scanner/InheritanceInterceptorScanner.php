<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

use Magento\Framework\ObjectManager\InterceptableValidator;

/**
 * Class \Magento\Setup\Module\Di\Code\Scanner\InheritanceInterceptorScanner
 *
 * @since 2.0.0
 */
class InheritanceInterceptorScanner implements ScannerInterface
{
    /**
     * @var InterceptableValidator
     * @since 2.1.0
     */
    private $interceptableValidator;

    /**
     * @param InterceptableValidator $interceptableValidator
     * @since 2.1.0
     */
    public function __construct(InterceptableValidator $interceptableValidator)
    {
        $this->interceptableValidator = $interceptableValidator;
    }

    /**
     * Get intercepted class names
     *
     * @param array $classes
     * @param array $interceptedEntities
     * @return array
     * @since 2.0.0
     */
    public function collectEntities(array $classes, array $interceptedEntities = [])
    {
        $output = [];
        foreach ($classes as $class) {
            foreach ($interceptedEntities as $interceptorClass) {
                $interceptedEntity = substr($interceptorClass, 0, -12);
                if (is_subclass_of($class, $interceptedEntity) && $this->interceptableValidator->validate($class)) {
                    $reflectionClass = new \ReflectionClass($class);
                    if (!$reflectionClass->isAbstract() && !$reflectionClass->isFinal()) {
                        $output[] = $class . '\\Interceptor';
                    }
                }
            }
        }
        $output = array_merge($this->filterOutAbstractClasses($interceptedEntities), $output);
        $output = array_unique($output);
        return $output;
    }

    /**
     * Filter out Interceptors defined for abstract classes
     *
     * @param string[] $interceptedEntities
     * @return string[]
     * @since 2.0.0
     */
    private function filterOutAbstractClasses($interceptedEntities)
    {
        $interceptedEntitiesFiltered = [];
        foreach ($interceptedEntities as $interceptorClass) {
            $interceptedEntity = substr($interceptorClass, 0, -12);
            $reflectionInterceptedEntity = new \ReflectionClass($interceptedEntity);
            if (!$reflectionInterceptedEntity->isAbstract() && !$reflectionInterceptedEntity->isFinal()) {
                $interceptedEntitiesFiltered[] = $interceptorClass;
            }
        }
        return $interceptedEntitiesFiltered;
    }
}
