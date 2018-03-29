<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * ItemStatusInterface interface
 * Temporary object with status of requested item.
 * Indicate if entity param was Accepted|Rejected to bulk schedule
 *
 * @api
 */
interface ItemStatusInterface
{
    const ENTITY_ID = 'entity_id';
    const DATA_HASH = 'data_hash';
    const STATUS = 'status';
    const ERROR_MESSAGE = 'error_message';
    const ERROR_CODE = 'error_code';

    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get entity Id.
     *
     * @return int
     */
    public function getId();

    /**
     * Sets entity Id.
     *
     * @param int $entityId
     * @return $this
     */
    public function setId($entityId);

    /**
     * Get hash of entity data.
     *
     * @return string md5 hash of entity params array.
     */
    public function getDataHash();

    /**
     * Sets hash of entity data.
     *
     * @param string $hash md5 hash of entity params array.
     * @return $this
     */
    public function setDataHash($hash);

    /**
     * Get status.
     *
     * @return string accepted|rejected
     */
    public function getStatus();

    /**
     * Sets entity status.
     *
     * @param string $status accepted|rejected
     * @return $this
     */
    public function setStatus($status = self::STATUS_ACCEPTED);

    /**
     * Get error information.
     *
     * @return string|null
     */
    public function getErrorMessage();

    /**
     * Sets error information.
     *
     * @param string|null|\Exception $error
     * @return $this
     */
    public function setErrorMessage($error = null);

    /**
     * Get error code.
     *
     * @return int|null
     */
    public function getErrorCode();

    /**
     * Sets error information.
     *
     * @param int|null|\Exception $errorCode Default: null
     * @return $this
     */
    public function setErrorCode($errorCode = null);
}
