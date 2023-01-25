<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Interception\Sample;

/**
 * Sample class
 */
class Entity
{
    /**
     * @var array
     */
    private $pluginCalls = [];

    /**
     * Sample method
     *
     * @return bool
     */
    public function doSomething()
    {
        $this->addPluginCall(self::class . '::' . __FUNCTION__);

        return true;
    }

    /**
     * Get plugin calls info for testing
     *
     * @return array
     */
    public function getPluginCalls()
    {
        return $this->pluginCalls;
    }

    /**
     * Add plugin call info for testing
     *
     * @param string $call
     * @return void
     */
    public function addPluginCall($call)
    {
        $this->pluginCalls[] = $call;
    }
}
