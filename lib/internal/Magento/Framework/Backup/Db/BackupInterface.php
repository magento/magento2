<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Db;

/**
 * @api
 *
 * @deprecated 101.0.7 Backups should be done using other means.
 * @since 100.0.2
 */
interface BackupInterface
{
    /**
     * Set backup time
     *
     * @param int $time
     * @return $this
     */
    public function setTime($time);

    /**
     * Set backup type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Set backup path
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path);

    /**
     * Set backup name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Open backup file (write or read mode)
     *
     * @param bool $write
     * @return $this
     */
    public function open($write = false);

    /**
     * Write to backup file
     *
     * @param string $data
     * @return $this
     */
    public function write($data);

    /**
     * Close open backup file
     *
     * @return $this
     */
    public function close();
}
