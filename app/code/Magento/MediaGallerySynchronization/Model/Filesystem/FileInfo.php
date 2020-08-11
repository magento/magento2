<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model\Filesystem;

/**
 * Internal class wrapping \SplFileInfo
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class FileInfo extends \SplFileInfo
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
     * @var string
     */
    private $pathname;

    /**
     * @var int
     */
    private $inode;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $owner;

    /**
     * @var int
     */
    private $group;

    /**
     * @var int
     */
    private $aTime;

    /**
     * @var int
     */
    private $mTime;

    /**
     * @var int
     */
    private $cTime;

    /**
     * @var string
     */
    private $type;

    /**
     * @var false|string
     */
    private $realPath;

    /**
     * FileInfo constructor.
     * @param string $file_name
     * @param string $path
     * @param string $filename
     * @param string $extension
     * @param string $basename
     * @param string $pathname
     * @param int $inode
     * @param int $size
     * @param int $owner
     * @param int $group
     * @param int $aTime
     * @param int $mTime
     * @param int $cTime
     * @param string $type
     * @param false|string $realPath
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $file_name,
        string $path,
        string $filename,
        string $extension,
        string $basename,
        string $pathname,
        int $inode,
        int $size,
        int $owner,
        int $group,
        int $aTime,
        int $mTime,
        int $cTime,
        string $type,
        $realPath
    ) {
        parent::__construct($file_name);
        $this->path = $path;
        $this->filename = $filename;
        $this->extension = $extension;
        $this->basename = $basename;
        $this->pathname = $pathname;
        $this->inode = $inode;
        $this->size = $size;
        $this->owner = $owner;
        $this->group = $group;
        $this->aTime = $aTime;
        $this->mTime = $mTime;
        $this->cTime = $cTime;
        $this->type = $type;
        $this->realPath = $realPath;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @inheritDoc
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @inheritDoc
     */
    public function getBasename($suffix = null): string
    {
        return $this->basename;
    }

    /**
     * @inheritDoc
     */
    public function getPathname(): string
    {
        return $this->pathname;
    }

    /**
     * @inheritDoc
     */
    public function getInode(): int
    {
        return $this->inode;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getOwner(): int
    {
        return $this->owner;
    }

    /**
     * @inheritDoc
     */
    public function getGroup(): int
    {
        return $this->group;
    }

    /**
     * @inheritDoc
     */
    public function getATime(): int
    {
        return $this->aTime;
    }

    /**
     * @inheritDoc
     */
    public function getMTime(): int
    {
        return $this->mTime;
    }

    /**
     * @inheritDoc
     */
    public function getCTime(): int
    {
        return $this->cTime;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getRealPath()
    {
        return $this->realPath;
    }
}
