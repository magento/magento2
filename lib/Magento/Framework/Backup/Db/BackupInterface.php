<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Backup\Db;

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
