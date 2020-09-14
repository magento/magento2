<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

use Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\MediaGalleryUiApi\Api\GetInsertImageDataInterface;

/**
 * Class responsible to provide insert image details
 */
class GetInsertImageData implements GetInsertImageDataInterface
{
    private $extensionAttributes;

    /**
     * @var GetInsertImageContent
     */
    private $getInsertImageContent;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var ReadInterface
     */
    private $pubDirectory;

    /**
     * GetInsertImageData constructor.
     *
     * @param GetInsertImageContent $getInsertImageContent
     * @param Filesystem $fileSystem
     * @param Mime $mime
     */
    public function __construct(
        GetInsertImageContent $getInsertImageContent,
        Filesystem $fileSystem,
        Mime $mime
    ) {
        $this->getInsertImageContent = $getInsertImageContent;
        $this->filesystem = $fileSystem;
        $this->mime = $mime;
    }

    /**
     * Retrieves a content (just a link or an html block) for inserting image to the content
     *
     * @param string $encodedFilename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     * @return null|string
     */
    public function getImageContent(
        string $encodedFilename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId = null
    ): string {
        return $this->getInsertImageContent->execute($encodedFilename, $forceStaticPath, $renderAsTag, $storeId);
    }

    /**
     * Retrieve size of requested file
     *
     * @param string $path
     * @return int
     */
    public function getFileSize(string $path): int
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
    public function getMimeType(string $path): string
    {
        $absoluteFilePath = $this->getPubDirectory()->getAbsolutePath($path);

        return $this->getPubDirectory()->isExist($path) ? $this->mime->getMimeType($absoluteFilePath) : '';
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

    /**
     * Get extension attributes
     *
     * @return ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes(): ?ExtensionAttributesInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * Set extension attributes
     *
     * @param ExtensionAttributesInterface|null $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(?ExtensionAttributesInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
