<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

class InheritanceInterceptorScanner implements ScannerInterface
{
    /**
     * Get intercepted class names
     *
     * @param array $classes
     * @param array $interceptedEntities
     * @return array
     */
    public function collectEntities(array $classes, array $interceptedEntities = [])
    {
        $output = [];
        $interceptedEntitiesFiltered = [];
        foreach ($classes as $class) {
            foreach ($interceptedEntities as $interceptorClass) {
                $interceptedEntity = substr($interceptorClass, 0, -12);
                $reflectionInterceptedEntity = new \ReflectionClass($interceptedEntity);
                if (!$reflectionInterceptedEntity->isAbstract() && !$reflectionInterceptedEntity->isFinal()) {
                    $interceptedEntitiesFiltered[] = $interceptorClass;
                }

                if (is_subclass_of($class, $interceptedEntity)
                    && !$this->endsWith($class, 'RepositoryInterface\\Proxy')
                    && !$this->endsWith($class, '\\Interceptor')) {
                    $reflectionClass = new \ReflectionClass($class);
                    if (!$reflectionClass->isAbstract() && !$reflectionClass->isFinal()) {
                        $output[] = $class . '\\Interceptor';
                    }
                }
            }
        }
        $output = array_merge($interceptedEntitiesFiltered, $output);
        $output = array_unique($output);
        return $output;
    }

    /**
     * Check if a string ends with a substring
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    private function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === ""
        || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}
