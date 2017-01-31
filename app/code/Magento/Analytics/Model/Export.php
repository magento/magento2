<?php
/**
 * Copyright © 2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Archive;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Export
 *
 * Execute data collection to $path
 * Packs collected data in $path and pack them into archive
 * Returns archive content
 */
class Export
{
    /**
     * Path for output files
     *
     * @var string
     */
    private $path = 'analytics/';

    /**
     * Name of temp archive
     *
     * @var string
     */
    private $archiveName = 'analytics.tgz';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Archive
     */
    private $archive;
    /**
     * @var \Magento\Analytics\Model\ReportWriterInterface
     */
    private $reportWriter;

    /**
     * Export constructor.
     *
     * @param Filesystem $filesystem
     * @param Archive $archive
     * @param ReportWriterInterface $reportWriter
     */
    public function __construct(
        Filesystem $filesystem,
        Archive $archive,
        ReportWriterInterface $reportWriter
    ) {
        $this->filesystem = $filesystem;
        $this->archive = $archive;
        $this->reportWriter = $reportWriter;
    }

    /**
     * Returns archive content
     *
     * @return string
     */
    public function getArchiveContent()
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $directory->delete($this->path);
        try {
            $this->reportWriter->write($directory, $this->path);
            $archiveFile = $directory->getAbsolutePath(). $this->archiveName;
            $this->archive->pack($directory->getAbsolutePath($this->path), $archiveFile, true);
        } finally {
            $directory->delete($this->path);
        }
        return $directory->readFile($this->archiveName);
    }
}
