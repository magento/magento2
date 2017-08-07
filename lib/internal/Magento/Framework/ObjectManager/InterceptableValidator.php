<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

/**
 * Class \Magento\Framework\ObjectManager\InterceptableValidator
 *
 * @since 2.0.1
 */
class InterceptableValidator
{
    /**
     * @param string $className
     * @return bool
     * @since 2.0.1
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
     * @since 2.0.1
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
     * @since 2.0.1
     */
    private function isInterceptable($instanceName)
    {
        return !is_subclass_of(
            $instanceName,
            '\\' . \Magento\Framework\ObjectManager\Code\Generator\Proxy::NON_INTERCEPTABLE_INTERFACE
        );
    }

    /**
     * Check if a string ends with a substring
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     * @since 2.0.1
     */
    private function endsWith($haystack, $needle)
    {
        // Search forward starting from end minus needle length characters
        $temp = strlen($haystack) - strlen($needle);
        return $needle === '' || ($temp >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}
