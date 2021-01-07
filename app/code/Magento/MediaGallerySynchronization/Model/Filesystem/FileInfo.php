<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model\Filesystem;

/**
 * Class for getting image file information.
 */
class FileInfo
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var $basename
     */
    private $basename;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $mTime;

    /**
     * @var int
     */
    private $cTime;

    /**
     * FileInfo constructor.
     *
     * @param string $path
     * @param string $filename
     * @param string $extension
     * @param string $basename
     * @param int $size
     * @param int $mTime
     * @param int $cTime
     */
    public function __construct(
        string $path,
        string $filename,
        string $extension,
        string $basename,
        int $size,
        int $mTime,
        int $cTime
    ) {
        $this->path = $path;
        $this->filename = $filename;
        $this->extension = $extension;
        $this->basename = $basename;
        $this->size = $size;
        $this->mTime = $mTime;
        $this->cTime = $cTime;
    }

    /**
     * Get path without filename.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Get file extension.
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Get file basename.
     *
     * @return string
     */
    public function getBasename(): string
    {
        return $this->basename;
    }

    /**
     * Get file size.
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Get last modified time.
     *
     * @return int
     */
    public function getMTime(): int
    {
        return $this->mTime;
    }

    /**
     * Get inode change time.
     *
     * @return int
     */
    public function getCTime(): int
    {
        return $this->cTime;
    }
}
