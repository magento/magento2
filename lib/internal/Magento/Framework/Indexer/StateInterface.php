<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * @api Retrieve status of the Indexer
 * @since 100.0.2
 */
interface StateInterface
{
    /**
     * Indexer statuses
     */
    public const STATUS_WORKING = 'working';
    public const STATUS_VALID = 'valid';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * Return indexer id
     *
     * @return string
     */
    public function getIndexerId();

    /**
     * Set indexer id
     *
     * @param string $value
     * @return $this
     */
    public function setIndexerId($value);

    /**
     * Return status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Return updated
     *
     * @return string
     */
    public function getUpdated();

    /**
     * Set updated
     *
     * @param string $value
     * @return $this
     */
    public function setUpdated($value);

    /**
     * Fill object with state data by view ID
     *
     * @param string $indexerId
     * @return $this
     */
    public function loadByIndexer($indexerId);

    /**
     * Status setter
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);
}
