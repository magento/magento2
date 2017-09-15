<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.2.0
 */
interface ReportWriterInterface
{
    /**
     * Writes report files to provided path
     *
     * @param WriteInterface $directory
     * @param string $path
     * @return void
     * @since 2.2.0
     */
    public function write(WriteInterface $directory, $path);
}
