<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Catalog\Model\ResourceModel\Product\MediaGalleryValue;
use Magento\Eav\Model\ResourceModel\AttributeValue;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\Helper\Data;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Create handler for catalog product gallery
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 101.0.0
 */
class CreateHandler implements ExtensionInterface
{
    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata
     * @since 101.0.0
     */
    protected $metadata;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface
     * @since 101.0.0
     */
    protected $attribute;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     * @since 101.0.0
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     * @since 101.0.0
     */
    protected $resourceModel;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     * @since 101.0.0
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     * @since 101.0.0
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 101.0.0
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     * @since 101.0.0
     */
    protected $fileStorageDb;

    /**
     * @var array
     */
    private $mediaAttributeCodes;

    /**
     * @var array
     */
    private $mediaEavCache;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DeleteValidator
     */
    private $deleteValidator;

    /**
     * @var MediaGalleryValue
     */
    private $mediaGalleryValue;

    /**
     * @var AttributeValue
     */
    private $attributeValue;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var string[]
     */
    private $mediaAttributesWithLabels = [
        'image',
        'small_image',
        'thumbnail'
    ];

    /**
     * @param MetadataPool $metadataPool
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param Gallery $resourceModel
     * @param Data $jsonHelper
     * @param Config $mediaConfig
     * @param Filesystem $filesystem
     * @param Database $fileStorageDb
     * @param StoreManagerInterface|null $storeManager
     * @param DeleteValidator|null $deleteValidator
     * @param MediaGalleryValue|null $mediaGalleryValue
     * @param AttributeValue|null $attributeValue
     * @param \Magento\Eav\Model\Config|null $config
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        MetadataPool $metadataPool,
        ProductAttributeRepositoryInterface $attributeRepository,
        Gallery $resourceModel,
        Data $jsonHelper,
        Config $mediaConfig,
        Filesystem $filesystem,
        Database $fileStorageDb,
        ?StoreManagerInterface $storeManager = null,
        ?DeleteValidator $deleteValidator = null,
        ?MediaGalleryValue $mediaGalleryValue = null,
        ?AttributeValue $attributeValue = null,
        ?\Magento\Eav\Model\Config $config = null
    ) {
        $this->metadata = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $this->attributeRepository = $attributeRepository;
        $this->resourceModel = $resourceModel;
        $this->jsonHelper = $jsonHelper;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileStorageDb = $fileStorageDb;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->deleteValidator = $deleteValidator ?: ObjectManager::getInstance()->get(DeleteValidator::class);
        $this->mediaGalleryValue = $mediaGalleryValue ?? ObjectManager::getInstance()->get(MediaGalleryValue::class);
        $this->attributeValue = $attributeValue ?? ObjectManager::getInstance()->get(AttributeValue::class);
        $this->eavConfig = $config ?? ObjectManager::getInstance()->get(\Magento\Eav\Model\Config::class);
    }

    /**
     * Execute create handler
     *
     * @param object $product
     * @param array $arguments
     * @return object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 101.0.0
     */
    public function execute($product, $arguments = [])
    {
        $this->mediaEavCache = null;
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
                if (!empty($image['removed']) && $this->deleteValidator->validate($product, $image['file'])) {
                    $image['removed'] = '';
                }

                if (!empty($image['removed'])) {
                    $clearImages[] = $image['file'];
                } elseif (empty($image['value_id']) || !empty($image['recreate'])) {
                    $newFile = $this->moveImageFromTmp($image['file'] ?? '');
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
                if (!empty($image['removed']) && $this->deleteValidator->validate($product, $image['file'])) {
                    $image['removed'] = '';
                }

                if (empty($image['value_id']) || !empty($image['removed'])) {
                    continue;
                }
                $duplicate[$image['value_id']] = $this->copyImage($image['file'] ?? '');
                $image['new_file'] = $duplicate[$image['value_id']];
                $newImages[$image['file']] = $image;
            }

            $value['duplicate'] = $duplicate;
        }

        if (!empty($value['images'])) {
            $this->processMediaAttributes($product, $existImages, $newImages, $clearImages);
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
     * Returns media gallery attribute instance
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     * @since 101.0.0
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
     * Process delete images
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $images
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 101.0.0
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    protected function processDeletedImages($product, array &$images)
    {
    }

    /**
     * Process images
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $images
     * @return void
     * @since 101.0.0
     */
    protected function processNewAndExistingImages($product, array &$images)
    {
        $existingGalleryStoreValues = $this->getExistingGalleryStoreValues($product);
        foreach ($images as &$image) {
            if (empty($image['removed'])) {
                $isNew = empty($image['value_id']);
                $data = $this->processNewImage($product, $image);

                // Add per store labels, position, disabled
                $data['value_id'] = (int) $image['value_id'];
                $data['label'] = !empty($image['label']) ? $image['label'] : null;
                $data['position'] = isset($image['position']) && $image['position'] !== ''
                    ? (int)$image['position']
                    : null;
                $data['disabled'] = isset($image['disabled']) ? (int)$image['disabled'] : 0;
                $data['store_id'] = (int)$product->getStoreId();

                $data[$this->metadata->getLinkField()] = (int)$product->getData($this->metadata->getLinkField());

                if (!$isNew) {
                    $data += (array) $this->getExistingGalleryStoreValue(
                        $existingGalleryStoreValues,
                        $data['value_id'],
                        $data['store_id']
                    );
                }

                $this->saveGalleryStoreValue($data, $isNew);
            }
        }
    }

    /**
     * Returns existing gallery store value by value id and store id
     *
     * @param array $existingGalleryStoreValues
     * @param int $valueId
     * @param int $storeId
     * @return array|null
     */
    private function getExistingGalleryStoreValue(array $existingGalleryStoreValues, int $valueId, int $storeId): ?array
    {
        foreach ($existingGalleryStoreValues as $existingGalleryStoreValue) {
            if (((int) $existingGalleryStoreValue['value_id']) === $valueId
                && ((int) $existingGalleryStoreValue['store_id']) === $storeId
            ) {
                return $existingGalleryStoreValue;
            }
        }

        return null;
    }

    /**
     * Get existing gallery store values
     *
     * @param Product $product
     * @return array
     * @throws \Exception
     */
    private function getExistingGalleryStoreValues(Product $product): array
    {
        $existingMediaGalleryValues = [];
        if (!$product->isObjectNew()) {
            $productId = (int)$product->getData($this->metadata->getLinkField());
            $existingMediaGalleryValues = $this->mediaGalleryValue->getAllByEntityId($productId);
        }
        return $existingMediaGalleryValues;
    }

    /**
     * Save media gallery store value
     *
     * @param array $data
     * @param bool $isNewImage
     */
    private function saveGalleryStoreValue(array $data, bool $isNewImage): void
    {
        $items = [];
        $items[] = $data;
        if ($isNewImage && $data['store_id'] !== Store::DEFAULT_STORE_ID) {
            $dataForDefaultScope = $data;
            $dataForDefaultScope['store_id'] = Store::DEFAULT_STORE_ID;
            $dataForDefaultScope['disabled'] = 0;
            $dataForDefaultScope['label'] = null;
            $items[] = $dataForDefaultScope;
        }

        foreach ($items as $item) {
            $this->mediaGalleryValue->saveGalleryStoreValue($item);
        }
    }

    /**
     * Processes image as new.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $image
     * @return array
     * @since 101.0.0
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
     * Duplicate attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @since 101.0.0
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
            $mediaGalleryData['duplicate'] ?? [],
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
     * @since 101.0.0
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
     * Returns safe filename for posted image
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
     * Returns file name according to tmp name
     *
     * @param string $file
     * @return string
     * @since 101.0.0
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
     * @since 101.0.0
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
            // phpcs:disable Magento2.Functions.DiscouragedFunction
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
     * @since 101.0.0
     */
    protected function copyImage($file)
    {
        try {
            $destinationFile = $this->getUniqueFileName($file);

            if (!$this->mediaDirectory->isFile($this->mediaConfig->getMediaPath($file))) {
                // phpcs:ignore Magento2.Exceptions.DirectThrow
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
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
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

    /**
     * Process media attribute
     *
     * @param Product $product
     * @param string $mediaAttrCode
     * @param array $clearImages
     * @param array $newImages
     */
    private function processMediaAttribute(
        Product $product,
        string $mediaAttrCode,
        array $clearImages,
        array $newImages
    ): void {
        $storeId = $this->getStoreIdForUpdate($product);
        $oldValue = $this->getMediaAttributeStoreValue($product, $mediaAttrCode, $storeId);
        // Prevent from breaking store inheritance
        if ($oldValue !== false || $storeId === Store::DEFAULT_STORE_ID) {
            $value = $product->hasData($mediaAttrCode) ? $product->getData($mediaAttrCode) : $oldValue;
            $newValue = $value;
            if (in_array($value, $clearImages)) {
                $newValue = 'no_selection';
            }
            if (in_array($value, array_keys($newImages))) {
                $newValue = $newImages[$value]['new_file'];
            }
            if ($oldValue !== $newValue) {
                $product->setData($mediaAttrCode, $newValue);
                $product->addAttributeUpdate(
                    $mediaAttrCode,
                    $newValue,
                    $storeId
                );
            }
        }
    }

    /**
     * Process media attribute label
     *
     * @param Product $product
     * @param string $mediaAttrCode
     * @param array $clearImages
     * @param array $newImages
     * @param array $existImages
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function processMediaAttributeLabel(
        Product $product,
        string $mediaAttrCode,
        array $clearImages,
        array $newImages,
        array $existImages
    ): void {
        $storeId = $this->getStoreIdForUpdate($product);
        $oldAttrLabelValue = $this->getMediaAttributeStoreValue($product, $mediaAttrCode . '_label', $storeId);

        $resetLabel = false;
        $attrData = $product->getData($mediaAttrCode);
        if (in_array($attrData, $clearImages)) {
            $product->setData($mediaAttrCode . '_label', null);
            $resetLabel = true;
        }

        if (in_array($attrData, array_keys($newImages))) {
            $product->setData($mediaAttrCode . '_label', $newImages[$attrData]['label']);
        }

        if (in_array($attrData, array_keys($existImages)) && isset($existImages[$attrData]['label'])) {
            $product->setData($mediaAttrCode . '_label', $existImages[$attrData]['label']);
            if ($existImages[$attrData]['label'] == null) {
                $resetLabel = true;
            }
        }

        if ($attrData === 'no_selection' && !empty($product->getData($mediaAttrCode . '_label'))) {
            $product->setData($mediaAttrCode . '_label', null);
            $resetLabel = true;
        }

        $newAttrLabelValue = $product->getData($mediaAttrCode . '_label');

        if ($newAttrLabelValue !== $oldAttrLabelValue && ($resetLabel || !empty($newAttrLabelValue))) {
            $product->addAttributeUpdate(
                $mediaAttrCode . '_label',
                $newAttrLabelValue,
                $storeId
            );
        }
    }

    /**
     * Get store id to update media attribute
     *
     * Attributes values are saved in "all store views" in single store mode
     *
     * @param Product $product
     * @return int
     * @see \Magento\Catalog\Model\ResourceModel\AbstractResource::_saveAttributeValue
     */
    private function getStoreIdForUpdate(Product $product): int
    {
        return $product->isObjectNew() || $this->storeManager->hasSingleStore()
            ? Store::DEFAULT_STORE_ID
            : (int) $product->getStoreId();
    }

    /**
     * Get all media attributes values
     *
     * @param Product $product
     * @return array
     */
    private function getMediaAttributesValues(Product $product): array
    {
        if ($this->mediaEavCache ===  null) {
            $attributeCodes = [];
            foreach ($this->mediaConfig->getMediaAttributeCodes() as $attributeCode) {
                $attributeCodes[] = $attributeCode;
                if (in_array($attributeCode, $this->mediaAttributesWithLabels)) {
                    $attributeCodes[] = $attributeCode . '_label';
                }
            }
            $this->mediaEavCache = $this->attributeValue->getValues(
                ProductInterface::class,
                (int) $product->getData($this->metadata->getLinkField()),
                $attributeCodes
            );
        }

        return $this->mediaEavCache;
    }

    /**
     * Get media attribute value for store view
     *
     * @param Product $product
     * @param string $attributeCode
     * @param int|null $storeId
     * @return mixed|false
     */
    private function getMediaAttributeStoreValue(
        Product $product,
        string $attributeCode,
        ?int $storeId = null
    ): mixed {
        $attributes = $this->eavConfig->getEntityAttributes(Product::ENTITY);
        $attributeId = $attributes[$attributeCode]->getAttributeId();
        $storeId = $storeId === null ? (int) $product->getStoreId() : $storeId;
        foreach ($this->getMediaAttributesValues($product) as $value) {
            if ($value['attribute_id'] === $attributeId && ((int)$value['store_id']) === $storeId) {
                return $value['value'];
            }
        }
        return false;
    }

    /**
     * Update media attributes
     *
     * @param Product $product
     * @param array $existImages
     * @param array $newImages
     * @param array $clearImages
     */
    private function processMediaAttributes(
        Product $product,
        array $existImages,
        array $newImages,
        array $clearImages
    ): void {
        foreach ($this->getMediaAttributeCodes() as $mediaAttrCode) {
            $this->processMediaAttribute(
                $product,
                $mediaAttrCode,
                $clearImages,
                $newImages
            );
            if (in_array($mediaAttrCode, $this->mediaAttributesWithLabels)) {
                $this->processMediaAttributeLabel(
                    $product,
                    $mediaAttrCode,
                    $clearImages,
                    $newImages,
                    $existImages
                );
            }
        }
    }
}
