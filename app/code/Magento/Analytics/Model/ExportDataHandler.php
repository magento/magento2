<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Archive;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class for the handling of a new data collection for MBI.
 */
class ExportDataHandler implements ExportDataHandlerInterface
{
    /**
     * Subdirectory path for all temporary files.
     *
     * @var string
     */
    private $subdirectoryPath = 'analytics/';

    /**
     * Filename of archive with collected data.
     *
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
     * Resource for write data of reports into separate files.
     *
     * @var ReportWriterInterface
     */
    private $reportWriter;

    /**
     * Resource for encrypting data.
     *
     * @var Cryptographer
     */
    private $cryptographer;

    /**
     * Resource for registration a new file.
     *
     * @var FileRecorder
     */
    private $fileRecorder;

    /**
     * @param Filesystem $filesystem
     * @param Archive $archive
     * @param ReportWriterInterface $reportWriter
     * @param Cryptographer $cryptographer
     * @param FileRecorder $fileRecorder
     */
    public function __construct(
        Filesystem $filesystem,
        Archive $archive,
        ReportWriterInterface $reportWriter,
        Cryptographer $cryptographer,
        FileRecorder $fileRecorder
    ) {
        $this->filesystem = $filesystem;
        $this->archive = $archive;
        $this->reportWriter = $reportWriter;
        $this->cryptographer = $cryptographer;
        $this->fileRecorder = $fileRecorder;
    }

    /**
     * @inheritdoc
     */
    public function prepareExportData()
    {
        try {
            $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

            $this->prepareDirectory($tmpDirectory, $this->getTmpFilesDirRelativePath());
            $this->reportWriter->write($tmpDirectory, $this->getTmpFilesDirRelativePath());

            $tmpFilesDirectoryAbsolutePath = $this->validateSource($tmpDirectory, $this->getTmpFilesDirRelativePath());
            $archiveAbsolutePath = $this->prepareFileDirectory($tmpDirectory, $this->getArchiveRelativePath());
            $this->pack(
                $tmpFilesDirectoryAbsolutePath,
                $archiveAbsolutePath
            );

            $this->validateSource($tmpDirectory, $this->getArchiveRelativePath());
            $this->fileRecorder->recordNewFile(
                $this->cryptographer->encode($tmpDirectory->readFile($this->getArchiveRelativePath()))
            );
        } finally {
            $tmpDirectory->delete($this->getTmpFilesDirRelativePath());
            $tmpDirectory->delete($this->getArchiveRelativePath());
        }

        return true;
    }

    /**
     * Return relative path to a directory for temporary files with reports data.
     *
     * @return string
     */
    private function getTmpFilesDirRelativePath()
    {
        return $this->subdirectoryPath . 'tmp/';
    }

    /**
     * Return relative path to a directory for an archive.
     *
     * @return string
     */
    private function getArchiveRelativePath()
    {
        return $this->subdirectoryPath . $this->archiveName;
    }

    /**
     * Clean up a directory.
     *
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
     * Remove a file and a create parent directory a file.
     *
     * @param WriteInterface $directory
     * @param string $path
     * @return string
     */
    private function prepareFileDirectory(WriteInterface $directory, $path)
    {
        $directory->delete($path);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (dirname($path) !== '.') {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $directory->create(dirname($path));
        }

        return $directory->getAbsolutePath($path);
    }

    /**
     * Packing data into an archive.
     *
     * @param string $source
     * @param string $destination
     * @return bool
     */
    private function pack($source, $destination)
    {
        $this->archive->pack(
            $source,
            $destination,
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            is_dir($source) ?: false
        );

        return true;
    }

    /**
     * Validate that data source exist.
     *
     * Return absolute path in a validated data source.
     *
     * @param WriteInterface $directory
     * @param string $path
     * @return string
     * @throws LocalizedException If source is not exist.
     */
    private function validateSource(WriteInterface $directory, $path)
    {
        if (!$directory->isExist($path)) {
            throw new LocalizedException(__('The "%1" source doesn\'t exist.', $directory->getAbsolutePath($path)));
        }

        return $directory->getAbsolutePath($path);
    }
}
