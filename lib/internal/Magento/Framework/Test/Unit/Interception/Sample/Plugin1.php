<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Interception\Sample;

/**
 * Sample plugin
 */
class Plugin1
{
    /**
     * Sample before-plugin method
     *
     * @return void
     */
    public function beforeDoSomething(Entity $subject)
    {
        $subject->addPluginCall(static::class . '::' . __FUNCTION__);
        //Not changing arguments
    }

    /**
     * Sample around-plugin method
     *
     * @param Entity $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundDoSomething(Entity $subject, \Closure $proceed)
    {
        $subject->addPluginCall(static::class . '::' . __FUNCTION__);
        //Not breaking the chain
        return $proceed();
    }

    /**
     * Sample after-plugin method
     *
     * @param Entity $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterDoSomething(Entity $subject, $result)
    {
        $subject->addPluginCall(static::class . '::' . __FUNCTION__);
        //Not changing result
        return $result;
    }
}
