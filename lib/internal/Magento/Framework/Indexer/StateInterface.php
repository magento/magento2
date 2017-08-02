<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * @api Retrieve status of the Indexer
 * @since 2.0.0
 */
interface StateInterface
{
    /**
     * Indexer statuses
     */
    const STATUS_WORKING = 'working';
    const STATUS_VALID = 'valid';
    const STATUS_INVALID = 'invalid';

    /**
     * Return indexer id
     *
     * @return string
     * @since 2.0.0
     */
    public function getIndexerId();

    /**
     * Set indexer id
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setIndexerId($value);

    /**
     * Return status
     *
     * @return string
     * @since 2.0.0
     */
    public function getStatus();

    /**
     * Return updated
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdated();

    /**
     * Set updated
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setUpdated($value);

    /**
     * Fill object with state data by view ID
     *
     * @param string $indexerId
     * @return $this
     * @since 2.0.0
     */
    public function loadByIndexer($indexerId);

    /**
     * Status setter
     *
     * @param string $status
     * @return $this
     * @since 2.0.0
     */
    public function setStatus($status);
}
