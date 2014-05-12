<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        call_user_func($this->_activationPolicy, array($this, 'displayStats'));
    }

    /**
     * Activate validation of the memory usage/leak limitations
     */
    public function activateLimitValidation()
    {
        call_user_func($this->_activationPolicy, array($this->_memoryLimit, 'validateUsage'));
    }
}
