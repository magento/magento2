<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\File\WriteInterface;

class GetInsertImageContent
{
    /**
     * @var ImagesHelper
     */
    private $imagesHelper;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var WriteInterface
     */
    private $pubDirectory;

    /**
     * @param ImagesHelper $imagesHelper
     * @param CatalogHelper $catalogHelper
     * @param Filesystem $fileSystem
     * @param Mime $mime
     */
    public function __construct(
        ImagesHelper $imagesHelper,
        CatalogHelper $catalogHelper,
        Filesystem $fileSystem,
        Mime $mime
    ) {
        $this->imagesHelper = $imagesHelper;
        $this->catalogHelper = $catalogHelper;
        $this->filesystem = $fileSystem;
        $this->mime = $mime;
    }

    /**
     * Create a content (just a link or an html block) for inserting image to the content
     *
     * @param string $encodedFilename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     * @return string
     */
    public function execute(
        string $encodedFilename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId = null
    ): string {
        $filename = $this->imagesHelper->idDecode($encodedFilename);

        $this->catalogHelper->setStoreId($storeId);
        $this->imagesHelper->setStoreId($storeId);

        if ($forceStaticPath) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            return parse_url($this->imagesHelper->getCurrentUrl() . $filename, PHP_URL_PATH);
        }

        return $this->imagesHelper->getImageHtmlDeclaration($filename, $renderAsTag);
    }

    /**
     * Retrieve size of requested file
     *
     * @param string $path
     * @return int
     */
    public function getImageSize(string $path): int
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
    public function getMimeType(string $path)
    {
        $absoluteFilePath = $this->getPubDirectory()->getAbsolutePath($path);

        return $this->mime->getMimeType($absoluteFilePath);
    }

    /**
     * Retrieve pub directory read interface instance
     *
     * @return ReadInterface
     */
    private function getPubDirectory()
    {
        if ($this->pubDirectory === null) {
            $this->pubDirectory = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
        }
        return $this->pubDirectory;
    }
}
