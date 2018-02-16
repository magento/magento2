<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Create handler for catalog product gallery
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateHandler implements ExtensionInterface
{
    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata
     */
    protected $metadata;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    protected $attribute;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * Resource model
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     */
    protected $resourceModel;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $fileStorageDb;

    /**
     * @var array
     */
    private $mediaAttributeCodes;

    /**
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
    ) {
        $this->metadata = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $this->attributeRepository = $attributeRepository;
        $this->resourceModel = $resourceModel;
        $this->jsonHelper = $jsonHelper;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileStorageDb = $fileStorageDb;
    }

    /**
     * @param object $product
     * @param array $arguments
     * @return object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute($product, $arguments = [])
    {
        $attrCode = $this->getAttribute()->getAttributeCode();

        $value = $product->getData($attrCode);

        if (!is_array($value) || !isset($value['images'])) {
            return $product;
        }

        if (!is_array($value['images']) && strlen($value['images']) > 0) {
            $value['images'] = $this->jsonHelper->jsonDecode($value['images']);
        }

        if (!is_array($value['images'])) {
            $value['images'] = [];
        }

        $clearImages = [];
        $newImages = [];
        $existImages = [];

        if ($product->getIsDuplicate() != true) {
            foreach ($value['images'] as &$image) {
                if (!empty($image['removed'])) {
                    $clearImages[] = $image['file'];
                } elseif (empty($image['value_id'])) {
                    $newFile = $this->moveImageFromTmp($image['file']);
                    $image['new_file'] = $newFile;
                    $newImages[$image['file']] = $image;
                    $image['file'] = $newFile;
                } else {
                    $existImages[$image['file']] = $image;
                }
            }
        } else {
            // For duplicating we need copy original images.
            $duplicate = [];
            foreach ($value['images'] as &$image) {
                if (empty($image['value_id']) || !empty($image['removed'])) {
                    continue;
                }
                $duplicate[$image['value_id']] = $this->copyImage($image['file']);
                $image['new_file'] = $duplicate[$image['value_id']];
                $newImages[$image['file']] = $image;
            }

            $value['duplicate'] = $duplicate;
        }

        /* @var $mediaAttribute \Magento\Catalog\Api\Data\ProductAttributeInterface */
        foreach ($this->getMediaAttributeCodes() as $mediaAttrCode) {
            $attrData = $product->getData($mediaAttrCode);
            if (empty($attrData) && empty($clearImages) && empty($newImages) && empty($existImages)) {
                continue;
            }
            if (in_array($attrData, $clearImages)) {
                $product->setData($mediaAttrCode, 'no_selection');
            }

            if (in_array($attrData, array_keys($newImages))) {
                $product->setData($mediaAttrCode, $newImages[$attrData]['new_file']);
                $product->setData($mediaAttrCode . '_label', $newImages[$attrData]['label']);
            }

            if (in_array($attrData, array_keys($existImages))) {
                $product->setData($mediaAttrCode . '_label', $existImages[$attrData]['label']);
            }

            $product->addAttributeUpdate(
                $mediaAttrCode,
                $product->getData($mediaAttrCode),
                $product->getStoreId()
            );
        }

        $product->setData($attrCode, $value);

        if ($product->getIsDuplicate() == true) {
            $this->duplicate($product);
            return $product;
        }

        if (!is_array($value) || !isset($value['images']) || $product->isLockedAttribute($attrCode)) {
            return $product;
        }

        $this->processDeletedImages($product, $value['images']);
        $this->processNewAndExistingImages($product, $value['images']);

        $product->setData($attrCode, $value);

        return $product;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    public function getAttribute()
    {
        if (!$this->attribute) {
            $this->attribute = $this->attributeRepository->get(
                'media_gallery'
            );
        }

        return $this->attribute;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $images
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function processDeletedImages($product, array &$images)
    {
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $images
     * @return void
     */
    protected function processNewAndExistingImages($product, array &$images)
    {
        foreach ($images as &$image) {
            if (empty($image['removed'])) {
                $data = $this->processNewImage($product, $image);

                $this->resourceModel->deleteGalleryValueInStore(
                    $image['value_id'],
                    $product->getData($this->metadata->getLinkField()),
                    $product->getStoreId()
                );

                // Add per store labels, position, disabled
                $data['value_id'] = $image['value_id'];
                $data['label'] = isset($image['label']) ? $image['label'] : '';
                $data['position'] = isset($image['position']) ? (int)$image['position'] : 0;
                $data['disabled'] = isset($image['disabled']) ? (int)$image['disabled'] : 0;
                $data['store_id'] = (int)$product->getStoreId();

                $data[$this->metadata->getLinkField()] = (int)$product->getData($this->metadata->getLinkField());

                $this->resourceModel->insertGalleryValueInStore($data);
            }
        }
    }

    /**
     * Processes image as new.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $image
     * @return array
     */
    protected function processNewImage($product, array &$image)
    {
        $data = [];

        $data['value'] = $image['file'];
        $data['attribute_id'] = $this->getAttribute()->getAttributeId();

        if (!empty($image['media_type'])) {
            $data['media_type'] = $image['media_type'];
        }

        $image['value_id'] = $this->resourceModel->insertGallery($data);

        $this->resourceModel->bindValueToEntity(
            $image['value_id'],
            $product->getData($this->metadata->getLinkField())
        );

        return $data;
    }

    /**
     * Duplicate product media gallery data.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function duplicate($product)
    {
        $mediaGalleryData = $product->getData(
            $this->getAttribute()->getAttributeCode()
        );

        if (!isset($mediaGalleryData['images']) || !is_array($mediaGalleryData['images'])) {
            return $this;
        }

        $this->resourceModel->duplicate(
            $this->getAttribute()->getAttributeId(),
            isset($mediaGalleryData['duplicate']) ? $mediaGalleryData['duplicate'] : [],
            $product->getOriginalLinkId(),
            $product->getData($this->metadata->getLinkField())
        );

        return $this;
    }

    /**
     * Move image from temporary directory to normal
     *
     * @param string $file
     * @return string
     */
    protected function moveImageFromTmp($file)
    {
        $file = $this->getFilenameFromTmp($this->getSafeFilename($file));
        $destinationFile = $this->getUniqueFileName($file);

        if ($this->fileStorageDb->checkDbUsage()) {
            $this->fileStorageDb->renameFile(
                $this->mediaConfig->getTmpMediaShortUrl($file),
                $this->mediaConfig->getMediaShortUrl($destinationFile)
            );

            $this->mediaDirectory->delete($this->mediaConfig->getTmpMediaPath($file));
            $this->mediaDirectory->delete($this->mediaConfig->getMediaPath($destinationFile));
        } else {
            $this->mediaDirectory->renameFile(
                $this->mediaConfig->getTmpMediaPath($file),
                $this->mediaConfig->getMediaPath($destinationFile)
            );
        }

        return str_replace('\\', '/', $destinationFile);
    }

    /**
     * Returns safe filename for posted image.
     *
     * @param string $file
     * @return string
     */
    private function getSafeFilename($file)
    {
        $file = DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);

        return $this->mediaDirectory->getDriver()->getRealPathSafety($file);
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getFilenameFromTmp($file)
    {
        return strrpos($file, '.tmp') == strlen($file) - 4 ? substr($file, 0, strlen($file) - 4) : $file;
    }

    /**
     * Check whether file to move exists. Getting unique name
     *
     * @param string $file
     * @param bool $forTmp
     * @return string
     */
    protected function getUniqueFileName($file, $forTmp = false)
    {
        if ($this->fileStorageDb->checkDbUsage()) {
            $destFile = $this->fileStorageDb->getUniqueFilename(
                $this->mediaConfig->getBaseMediaUrlAddition(),
                $file
            );
        } else {
            $destinationFile = $forTmp
                ? $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getTmpMediaPath($file))
                : $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getMediaPath($file));
            $destFile = dirname($file) . '/' . FileUploader::getNewFileName($destinationFile);
        }

        return $destFile;
    }

    /**
     * Copy image and return new filename.
     *
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function copyImage($file)
    {
        try {
            $destinationFile = $this->getUniqueFileName($file);

            if (!$this->mediaDirectory->isFile($this->mediaConfig->getMediaPath($file))) {
                throw new \Exception();
            }

            if ($this->fileStorageDb->checkDbUsage()) {
                $this->fileStorageDb->copyFile(
                    $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getMediaShortUrl($file)),
                    $this->mediaConfig->getMediaShortUrl($destinationFile)
                );
                $this->mediaDirectory->delete($this->mediaConfig->getMediaPath($destinationFile));
            } else {
                $this->mediaDirectory->copyFile(
                    $this->mediaConfig->getMediaPath($file),
                    $this->mediaConfig->getMediaPath($destinationFile)
                );
            }

            return str_replace('\\', '/', $destinationFile);
        } catch (\Exception $e) {
            $file = $this->mediaConfig->getMediaPath($file);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We couldn\'t copy file %1. Please delete media with non-existing images and try again.', $file)
            );
        }
    }

    /**
     * Get Media Attribute Codes cached value
     *
     * @return array
     */
    private function getMediaAttributeCodes()
    {
        if ($this->mediaAttributeCodes === null) {
            $this->mediaAttributeCodes = $this->mediaConfig->getMediaAttributeCodes();
        }
        return $this->mediaAttributeCodes;
    }
}
