<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Bootstrap of the memory monitoring
 */
namespace Magento\TestFramework\Bootstrap;

class Memory
{
    /**
     * Policy to perform requested actions on shutdown
     */
    const POLICY_SHUTDOWN = 'register_shutdown_function';

    /**
     * @var \Magento\TestFramework\MemoryLimit
     */
    private $_memoryLimit;

    /**
     * @var callable
     */
    private $_activationPolicy;

    /**
     * @param \Magento\TestFramework\MemoryLimit $memoryLimit
     * @param callable|string $activationPolicy
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Magento\TestFramework\MemoryLimit $memoryLimit,
        $activationPolicy = self::POLICY_SHUTDOWN
    ) {
        if (!is_callable($activationPolicy)) {
            throw new \InvalidArgumentException('Activation policy is expected to be a callable.');
        }
        $this->_memoryLimit = $memoryLimit;
        $this->_activationPolicy = $activationPolicy;
    }

    /**
     * Display memory usage statistics
     */
    public function displayStats()
    {
        echo $this->_memoryLimit->printHeader() . $this->_memoryLimit->printStats() . PHP_EOL;
    }

    /**
     * Activate displaying of the memory usage statistics
     */
    public function activateStatsDisplaying()
    {
        call_user_func($this->_activationPolicy, [$this, 'displayStats']);
    }

    /**
     * Activate validation of the memory usage/leak limitations
     */
    public function activateLimitValidation()
    {
        call_user_func($this->_activationPolicy, [$this->_memoryLimit, 'validateUsage']);
    }
}
