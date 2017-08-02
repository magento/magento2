<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Filesystem\Rollback;

/**
 * Filesystem rollback workers abstract class
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractRollback
{
    /**
     * Snapshot object
     *
     * @var \Magento\Framework\Backup\Filesystem
     * @since 2.0.0
     */
    protected $_snapshot;

    /**
     * Default worker constructor
     *
     * @param \Magento\Framework\Backup\Filesystem $snapshotObject
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Backup\Filesystem $snapshotObject)
    {
        $this->_snapshot = $snapshotObject;
    }

    /**
     * Main worker's function that makes files rollback
     *
     * @return void
     * @since 2.0.0
     */
    abstract public function run();
}
