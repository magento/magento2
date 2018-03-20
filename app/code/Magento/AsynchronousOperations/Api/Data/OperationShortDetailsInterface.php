<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api\Data;

use \Magento\Framework\Bulk\OperationInterface;
/**
 * Getter Class OperationShortDetailsInterface
 * Instead of OperationInterface this class don't provide all operation data
 * and not responsive to set any data, just get operation data without serialized_data
 *
 * @api
 * @since 100.3.0
 * @see \Magento\AsynchronousOperations\Api\Data\OperationInterface
 */
interface OperationShortDetailsInterface
{
    /**
     * Operation id
     *
     * @return int
     * @since 100.3.0
     */
    public function getId();

    /**
     * Message Queue Topic
     *
     * @return string
     * @since 100.3.0
     */
    public function getTopicName();

    /**
     * Result serialized Data
     *
     * @return string
     * @since 100.3.0
     */
    public function getResultSerializedData();

    /**
     * Get operation status
     *
     * OPEN | COMPLETE | RETRIABLY_FAILED | NOT_RETRIABLY_FAILED
     *
     * @return int
     * @since 100.3.0
     */
    public function getStatus();

    /**
     * Get result message
     *
     * @return string
     * @since 100.3.0
     */
    public function getResultMessage();

    /**
     * Get error code
     *
     * @return int
     * @since 100.3.0
     */
    public function getErrorCode();
}
