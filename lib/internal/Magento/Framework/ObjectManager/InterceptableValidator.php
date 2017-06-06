<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

class InterceptableValidator
{
    /**
     * @param string $className
     * @return bool
     */
    public function validate($className)
    {
        return !$this->isInterceptor($className) && $this->isInterceptable($className);
    }

    /**
     *
     * Check if instance type is interceptor
     *
     * @param string $instanceName
     * @return bool
     */
    private function isInterceptor($instanceName)
    {
        return $this->endsWith($instanceName, '\Interceptor');
    }

    /**
     *
     * Check if instance type is interceptable
     *
     * @param string $instanceName
     * @return bool
     */
    private function isInterceptable($instanceName)
    {
        try {
            /**
             * Catch exceptions for virtual classes on autoloading:
             *      Source class "...Collection" for "...CollectionFactory" generation does not exist.
             */
            $result = !is_subclass_of(
                $instanceName,
                \Magento\Framework\ObjectManager\Code\Generator\Proxy::NON_INTERCEPTABLE_INTERFACE
            );
        } catch (\RuntimeException $e) {
            $result = true; // interceptable by default
        }
        return $result;
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
        // Search forward starting from end minus needle length characters
        $temp = strlen($haystack) - strlen($needle);
        return $needle === '' || ($temp >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}
