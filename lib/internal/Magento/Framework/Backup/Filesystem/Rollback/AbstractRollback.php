<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup\Filesystem\Rollback;

use Magento\Framework\Backup\Filesystem;

/**
 * Filesystem rollback workers abstract class
 *
 * @api
 * @since 100.0.2
 */
abstract class AbstractRollback
{
    /**
     * Snapshot object
     *
     * @var Filesystem
     */
    protected $_snapshot;

    /**
     * Default worker constructor
     *
     * @param Filesystem $snapshotObject
     */
    public function __construct(Filesystem $snapshotObject)
    {
        $this->_snapshot = $snapshotObject;
    }

    /**
     * Main worker's function that makes files rollback
     *
     * @return void
     */
    abstract public function run();
}
