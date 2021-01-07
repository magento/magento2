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
        return !$this->isInterceptor($className) && !$this->isProxy($className) && $this->isInterceptable($className);
    }

    /**
     * Check if instance type is interceptor
     *
     * @param string $instanceName
     * @return bool
     */
    private function isInterceptor(string $instanceName): bool
    {
        return $this->endsWith($instanceName, '\Interceptor');
    }

    /**
     * Check if instance type is proxy
     *
     * @param string $instanceName
     * @return bool
     */
    private function isProxy(string $instanceName): bool
    {
        return $this->endsWith($instanceName, '\Proxy');
    }

    /**
     *
     * Check if instance type is interceptable
     *
     * @param string $instanceName
     * @return bool
     */
    private function isInterceptable(string $instanceName): bool
    {
        return !is_subclass_of(
            $instanceName,
            \Magento\Framework\ObjectManager\NoninterceptableInterface::class
        );
    }

    /**
     * Check if a string ends with a substring
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    private function endsWith(string $haystack, string $needle): bool
    {
        // Search forward starting from end minus needle length characters
        $temp = strlen($haystack) - strlen($needle);
        return $needle === '' || ($temp >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}
