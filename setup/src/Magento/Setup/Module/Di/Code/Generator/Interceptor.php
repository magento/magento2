<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Di\Code\Generator;

use Magento\Framework\Interception\Code\Generator\Interceptor as FrameworkInterceptor;

class Interceptor extends FrameworkInterceptor
{
    /**
     * Intercepted methods list
     *
     * @var array
     */
    private $interceptedMethods = [];

    /**
     * Whether method is intercepted
     *
     * @param \ReflectionMethod $method
     *
     * @return bool
     */
    protected function isInterceptedMethod(\ReflectionMethod $method)
    {
        return parent::isInterceptedMethod($method) && in_array($method->getName(), $this->interceptedMethods);
    }

    /**
     * Sets list of intercepted methods
     *
     * @param array $interceptedMethods
     *
     * @return void
     */
    public function setInterceptedMethods($interceptedMethods)
    {
        $this->interceptedMethods = $interceptedMethods;
    }
}
