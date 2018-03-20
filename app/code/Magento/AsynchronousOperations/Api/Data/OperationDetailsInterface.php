<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * Interface
 *
 * @api
 * @since 100.3.0
 */
interface OperationDetailsInterface
{
    /**
     * Total operations count
     *
     * @return int
     * @since 100.3.0
     */
    public function getOperationsTotal();

    /**
     * Open operations count
     *
     * @return int
     * @since 100.3.0
     */
    public function getOpen();

    /**
     * Successfully completed operations count
     *
     * @return int
     * @since 100.3.0
     */
    public function getOperationsSuccessful();

    /**
     * Total failed operations count
     *
     * @return int
     * @since 100.3.0
     */
    public function getTotalFailed();

    /**
     * Failed not retriable operations count
     *
     * @return int
     * @since 100.3.0
     */
    public function getFailedNotRetriable();

    /**
     * Failed retriable operations count
     *
     * @return int
     * @since 100.3.0
     */
    public function getFailedRetriable();

    /**
     * Rejected operations count
     *
     * @return int
     * @since 100.3.0
     */
    public function getRejected();
}
