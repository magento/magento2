<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

/**
 * Process media gallery and delete media image after product delete
 */
class MediaImageDeleteProcessor
{
    /**
     * @var MediaConfig
     */
    private $imageConfig;

    /**
     * @var Filesystem
     */
    private $mediaDirectory;

    /**
     * @var Processor
     */
    private $imageProcessor;

    /**
     * @var Gallery
     */
    private $productGallery;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Product constructor.
     *
     * @param MediaConfig $imageConfig
     * @param Filesystem $filesystem
     * @param Processor $imageProcessor
     * @param Gallery $productGallery
     * @param LoggerInterface $logger
     * @throws FileSystemException
     */
    public function __construct(
        MediaConfig $imageConfig,
        Filesystem $filesystem,
        Processor $imageProcessor,
        Gallery $productGallery,
        LoggerInterface $logger
    ) {
        $this->imageConfig = $imageConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageProcessor = $imageProcessor;
        $this->productGallery = $productGallery;
        $this->logger = $logger;
    }

    /**
     * Process $product data and remove image from gallery after product delete
     *
     * @param DataObject $product
     * @return void
     */
    public function execute(DataObject $product): void
    {
        try {
            $productImages = $product->getMediaGalleryImages();
            foreach ($productImages as $image) {
                $imageFile = $image->getFile();
                if ($imageFile) {
                    $this->deleteProductImage($image, $product, $imageFile);
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Check if image exists and is not used by any other products
     *
     * @param string $file
     * @return bool
     */
    private function canDeleteImage(string $file): bool
    {
        return $this->productGallery->countImageUses($file) < 1;
    }

    /**
     * Delete the physical image if it's existed and not used by other products
     *
     * @param string $imageFile
     * @param string $filePath
     * @throws FileSystemException
     */
    private function deletePhysicalImage(string $imageFile, string $filePath): void
    {
        if ($this->canDeleteImage($imageFile)) {
            $this->mediaDirectory->delete($filePath);
        }
    }

    /**
     * Remove product image
     *
     * @param DataObject $image
     * @param ProductInterface $product
     * @param string $imageFile
     */
    private function deleteProductImage(
        DataObject $image,
        ProductInterface $product,
        string $imageFile
    ): void {
        $catalogPath = $this->imageConfig->getBaseMediaPath();
        $filePath = $catalogPath . $imageFile;

        if ($this->mediaDirectory->isFile($filePath)) {
            try {
                $this->productGallery->deleteGallery($image->getValueId());
                $this->imageProcessor->removeImage($product, $imageFile);
                $this->deletePhysicalImage($imageFile, $filePath);
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
