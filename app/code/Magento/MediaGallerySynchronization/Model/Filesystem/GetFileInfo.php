<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model\Filesystem;

use Magento\MediaGallerySynchronization\Model\Filesystem\FileInfoFactory;

/**
 * Get file information
 */
class GetFileInfo
{
    /**
     * @var FileInfoFactory
     */
    private $fileInfoFactory;

    /**
     * GetFileInfo constructor.
     * @param FileInfoFactory $fileInfoFactory
     */
    public function __construct(
        FileInfoFactory $fileInfoFactory
    ) {
        $this->fileInfoFactory = $fileInfoFactory;
    }

    /**
     * Get file information based on path provided.
     *
     * @param string $path
     * @return FileInfo
     */
    public function execute(string $path): FileInfo
    {
        $splFileInfo = new \SplFileInfo($path);

        return $this->fileInfoFactory->create([
            'file_name' => $path,
            'path' => $splFileInfo->getPath(),
            'filename' => $splFileInfo->getFilename(),
            'extension' => $splFileInfo->getExtension(),
            'basename' => $splFileInfo->getBasename(),
            'pathname' => $splFileInfo->getPathname(),
            'perms' => $splFileInfo->getPerms(),
            'inode' => $splFileInfo->getInode(),
            'size' => $splFileInfo->getSize(),
            'owner' => $splFileInfo->getOwner(),
            'group' => $splFileInfo->getGroup(),
            'aTime' => $splFileInfo->getATime(),
            'mTime' => $splFileInfo->getMTime(),
            'cTime' => $splFileInfo->getCTime(),
            'type' => $splFileInfo->getType(),
            'realPath' => $splFileInfo->getRealPath(),
            'fileInfo' => $splFileInfo->getFileInfo(),
            'pathInfo' => $splFileInfo->getPathInfo()
        ]);
    }
}
