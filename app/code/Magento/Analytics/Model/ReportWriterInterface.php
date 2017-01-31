<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Interface ReportWriterInterface
 *
 * Writes report files
 * Executes export of collected data
 * Iterates registered providers @see etc/analytics.xml
 * Collects data (to TMP folder)
 */
interface ReportWriterInterface
{
    /**
     * Writes report files to provided path
     *
     * @param WriteInterface $directory
     * @param string $path
     * @return void
     */
    public function write(WriteInterface $directory, $path);
}
