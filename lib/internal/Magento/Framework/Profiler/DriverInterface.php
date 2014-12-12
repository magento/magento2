<?php
/**
 * Interface for profiler driver.
 *
 * Implementation of this interface is responsible for logic of profiling.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Profiler;

interface DriverInterface
{
    /**
     * Start timer
     *
     * @param string $timerId
     * @param array|null $tags
     * @return void
     */
    public function start($timerId, array $tags = null);

    /**
     * Stop timer
     *
     * @param string $timerId
     * @return void
     */
    public function stop($timerId);

    /**
     * Clear collected statistics for specified timer or for whole profiler if timer name is omitted.
     *
     * @param string|null $timerId
     * @return void
     */
    public function clear($timerId = null);
}
