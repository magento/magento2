<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\InsertImageData;

use Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\MediaGalleryUi\Model\InsertImageDataFactory;
use Magento\MediaGalleryUi\Model\InsertImageDataInterface;

class GetInsertImageData
{
    /**
     * @var ReadInterface
     */
    private $pubDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var GetInsertImageContent
     */
    private $getInsertImageContent;

    /**
     * @var InsertImageDataFactory
     */
    private $insertImageDataFactory;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * GetInsertImageData constructor.
     *
     * @param GetInsertImageContent $getInsertImageContent
     * @param Filesystem $fileSystem
     * @param Mime $mime
     * @param InsertImageDataFactory $insertImageDataFactory
     */
    public function __construct(
        GetInsertImageContent $getInsertImageContent,
        Filesystem $fileSystem,
        Mime $mime,
        InsertImageDataFactory $insertImageDataFactory
    ) {
        $this->getInsertImageContent = $getInsertImageContent;
        $this->filesystem = $fileSystem;
        $this->mime = $mime;
        $this->insertImageDataFactory = $insertImageDataFactory;
    }

    /**
     * Returns image data object
     *
     * @param string $encodedFilename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     * @return InsertImageDataInterface
     */
    public function execute(
        string $encodedFilename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId = null
    ): InsertImageDataInterface {
        $content = $this->getInsertImageContent->execute(
            $encodedFilename,
            $forceStaticPath,
            $renderAsTag,
            $storeId
        );
        $size = $forceStaticPath ? $this->getSize($content) : 0;
        $type = $forceStaticPath ? $this->getType($content) : '';
        return $this->insertImageDataFactory->create([
            'content' => $content,
            'size' => $size,
            'type' => $type
        ]);
    }

    /**
     * Retrieve size of requested file
     *
     * @param string $path
     * @return int
     */
    private function getSize(string $path): int
    {
        $directory = $this->getPubDirectory();

        return $directory->isExist($path) ? $directory->stat($path)['size'] : 0;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $path
     * @return string
     */
    public function getType(string $path): string
    {
        $fileExist = $this->getPubDirectory()->isExist($path);

        return $fileExist ? $this->mime->getMimeType($this->getPubDirectory()->getAbsolutePath($path)) : '';
    }

    /**
     * Retrieve pub directory read interface instance
     *
     * @return ReadInterface
     */
    private function getPubDirectory(): ReadInterface
    {
        if ($this->pubDirectory === null) {
            $this->pubDirectory = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
        }
        return $this->pubDirectory;
    }
}
