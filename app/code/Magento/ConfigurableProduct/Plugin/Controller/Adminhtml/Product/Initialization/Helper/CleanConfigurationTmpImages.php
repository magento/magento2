<?php
/**
 * Product initialization helper
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Controller\Adminhtml\Product\Initialization\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class cleaning configuration tmp images
 */
class CleanConfigurationTmpImages
{
    /**
     * @var Database
     */
    private $fileStorageDb;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param Database $fileStorageDb
     * @param MediaConfig $mediaConfig
     * @param Filesystem $filesystem
     */
    public function __construct(
        RequestInterface $request,
        Database $fileStorageDb,
        MediaConfig $mediaConfig,
        Filesystem $filesystem
    ) {
        $this->request = $request;
        $this->fileStorageDb = $fileStorageDb;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Clean Tmp configurable images
     *
     * @param Helper $subject
     * @param Product $configurableProduct
     *
     * @return Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitialize(Helper $subject, Product $configurableProduct): Product
    {
        // Clean tmp
        $configurations = $this->getConfigurations();
        foreach ($configurations as $simpleProductData) {
            if (!isset($simpleProductData['media_gallery']['images'])) {
                continue;
            }
            foreach ($simpleProductData['media_gallery']['images'] as $image) {
                $file = $this->getFilenameFromTmp($image['file']);
                if ($this->fileStorageDb->checkDbUsage()) {
                    $filename = $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getTmpMediaShortUrl($file));
                    $this->fileStorageDb->deleteFile($filename);
                } else {
                    $filename = $this->mediaConfig->getTmpMediaPath($file);
                    $this->mediaDirectory->delete($filename);
                }
            }
        }

        return $configurableProduct;
    }

    /**
     * Trim .tmp ending from filename
     *
     * @param string $file
     *
     * @return string
     */
    private function getFilenameFromTmp(string $file): string
    {
        return strrpos($file, '.tmp') == strlen($file) - 4 ? substr($file, 0, strlen($file) - 4) : $file;
    }

    /**
     * Get configurations from request
     *
     * @return array
     */
    private function getConfigurations(): array
    {
        $result = [];
        $configurableMatrix = $this->request->getParam('configurable-matrix-serialized', "[]");
        if (!empty($configurableMatrix)) {
            $configurableMatrix = json_decode($configurableMatrix, true);
            foreach ($configurableMatrix as $item) {
                if (empty($item['was_changed']) && empty($item['newProduct'])) {
                    continue;
                }
                $result[] = $item;
            }
        }

        return $result;
    }
}
