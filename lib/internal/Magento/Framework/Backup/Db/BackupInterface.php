<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Db;

/**
 * @api
 * @since 2.0.0
 */
interface BackupInterface
{
    /**
     * Set backup time
     *
     * @param int $time
     * @return $this
     * @since 2.0.0
     */
    public function setTime($time);

    /**
     * Set backup type
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type);

    /**
     * Set backup path
     *
     * @param string $path
     * @return $this
     * @since 2.0.0
     */
    public function setPath($path);

    /**
     * Set backup name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Open backup file (write or read mode)
     *
     * @param bool $write
     * @return $this
     * @since 2.0.0
     */
    public function open($write = false);

    /**
     * Write to backup file
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function write($data);

    /**
     * Close open backup file
     *
     * @return $this
     * @since 2.0.0
     */
    public function close();
}
