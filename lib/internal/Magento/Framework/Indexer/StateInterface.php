<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

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
