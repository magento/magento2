<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Archive;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class ExportDataHandler
{
    /**
     * Subdirectory for temporary files.
     *
     * @var string
     */
    private $subdirectoryPath = 'analytics/';

    /**
     * @var string
     */
    private $archiveName = 'data.tgz';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Archive
     */
    private $archive;

    /**
     * @var ReportWriterInterface
     */
    private $reportWriter;

    /**
     * @var Coder
     */
    private $coder;

    /**
     * @var FileRecorder
     */
    private $fileRecorder;

    /**
     * @param Filesystem $filesystem
     * @param Archive $archive
     * @param ReportWriterInterface $reportWriter
     * @param Coder $coder
     * @param FileRecorder $fileRecorder
     */
    public function __construct(
        Filesystem $filesystem,
        Archive $archive,
        ReportWriterInterface $reportWriter,
        Coder $coder,
        FileRecorder $fileRecorder
    ) {
        $this->filesystem = $filesystem;
        $this->archive = $archive;
        $this->reportWriter = $reportWriter;
        $this->coder = $coder;
        $this->fileRecorder = $fileRecorder;
    }

    /**
     * @return bool
     */
    public function prepareExportData()
    {
        try {
            $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);

            $tmpFilesDirAbsolutePath = $this->prepareDirectory($tmpDirectory, $this->getTmpFilesDirRelativePath());
            $this->reportWriter->write($tmpDirectory, $this->getTmpFilesDirRelativePath());

            $this->validateSource($tmpDirectory, $this->getTmpFilesDirRelativePath());
            $archiveAbsolutePath = $this->prepareFileDirectory($tmpDirectory, $this->getArchiveRelativePath());
            $this->pack(
                $tmpFilesDirAbsolutePath,
                $archiveAbsolutePath
            );

            $this->validateSource($tmpDirectory, $this->getArchiveRelativePath());
            $this->fileRecorder->recordNewFile(
                $this->coder->encode(file_get_contents($archiveAbsolutePath))
            );
        } finally {
            $tmpDirectory->delete($this->getTmpFilesDirRelativePath());
            $tmpDirectory->delete($this->getArchiveRelativePath());
        }

        return true;
    }

    /**
     * @return string
     */
    public function getTmpFilesDirRelativePath()
    {
        return $this->subdirectoryPath . 'tmp/';
    }

    /**
     * @return string
     */
    public function getArchiveRelativePath()
    {
        return $this->subdirectoryPath . $this->archiveName;
    }

    /**
     * @param WriteInterface $directory
     * @param string $path
     * @return string
     */
    private function prepareDirectory(WriteInterface $directory, $path)
    {
        $directory->delete($path);

        return $directory->getAbsolutePath($path);
    }

    /**
     * @param WriteInterface $directory
     * @param string $path
     * @return string
     */
    private function prepareFileDirectory(WriteInterface $directory, $path)
    {
        $directory->delete($path);
        if (dirname($path) !== '.') {
            $directory->create(dirname($path));
        }

        return $directory->getAbsolutePath($path);
    }

    /**
     * @param string $source
     * @param $destination
     * @return bool
     */
    private function pack($source, $destination)
    {
        $this->archive->pack(
            $source,
            $destination,
            is_dir($source) ?: false
        );

        return true;
    }

    /**
     * @param WriteInterface $directory
     * @param $path
     * @return bool
     * @throws LocalizedException
     */
    private function validateSource(WriteInterface $directory, $path)
    {
        if (!$directory->isExist($path)) {
            throw new LocalizedException(__(''));
        }

        return true;
    }
}
