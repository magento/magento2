<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export;

use Magento\Framework\Filesystem\Io\File;

/**
 * Export file naming and filtering rules.
 */
class FileInfo
{
    /**
     * Suffix used for in-progress export files.
     */
    public const IN_PROGRESS_FILE_SUFFIX = '.tmp';

    /**
     * @var ConfigInterface
     */
    private $exportConfig;

    /**
     * @var File
     */
    private $fileIo;

    /**
     * @param ConfigInterface $exportConfig
     * @param File $fileIo
     */
    public function __construct(ConfigInterface $exportConfig, File $fileIo)
    {
        $this->exportConfig = $exportConfig;
        $this->fileIo = $fileIo;
    }

    /**
     * Get temporary export file path for queue-based exports.
     *
     * @param string $fileName
     * @return string
     */
    public function getInProgressFilePath(string $fileName): string
    {
        return 'export/' . $fileName . self::IN_PROGRESS_FILE_SUFFIX;
    }

    /**
     * Check whether file is an in-progress export artifact.
     *
     * @param string $filePath
     * @return bool
     */
    public function isInProgressFile(string $filePath): bool
    {
        return str_ends_with($filePath, self::IN_PROGRESS_FILE_SUFFIX);
    }

    /**
     * Check whether filename is a final export file.
     *
     * @param string $fileName
     * @return bool
     */
    public function isExportFile(string $fileName): bool
    {
        return isset($this->exportConfig->getFileFormats()[$this->fileIo->getPathInfo($fileName)['extension'] ?? '']);
    }
}
