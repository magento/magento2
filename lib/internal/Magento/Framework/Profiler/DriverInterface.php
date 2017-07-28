<?php
/**
 * Interface for profiler driver.
 *
 * Implementation of this interface is responsible for logic of profiling.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler;

/**
 * @api
 * @since 2.0.0
 */
interface DriverInterface
{
    /**
     * Start timer
     *
     * @param string $timerId
     * @param array|null $tags
     * @return void
     * @since 2.0.0
     */
    public function start($timerId, array $tags = null);

    /**
     * Stop timer
     *
     * @param string $timerId
     * @return void
     * @since 2.0.0
     */
    public function stop($timerId);

    /**
     * Clear collected statistics for specified timer or for whole profiler if timer name is omitted.
     *
     * @param string|null $timerId
     * @return void
     * @since 2.0.0
     */
    public function clear($timerId = null);
}
