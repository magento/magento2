<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\ResourceModel\AttributeValue;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\Helper\Data;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Update handler for catalog product gallery.
 *
 * @api
 * @since 101.0.0
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateHandler extends CreateHandler
{
    /**
     * @var AttributeValue
     */
    private $attributeValue;

    /**
     * @param MetadataPool $metadataPool
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param Gallery $resourceModel
     * @param Data $jsonHelper
     * @param Config $mediaConfig
     * @param Filesystem $filesystem
     * @param Database $fileStorageDb
     * @param StoreManagerInterface|null $storeManager
     * @param AttributeValue|null $attributeValue
     */
    public function __construct(
        MetadataPool $metadataPool,
        ProductAttributeRepositoryInterface $attributeRepository,
        Gallery $resourceModel,
        Data $jsonHelper,
        Config $mediaConfig,
        Filesystem $filesystem,
        Database $fileStorageDb,
        StoreManagerInterface $storeManager = null,
        ?AttributeValue $attributeValue = null
    ) {
        parent::__construct(
            $metadataPool,
            $attributeRepository,
            $resourceModel,
            $jsonHelper,
            $mediaConfig,
            $filesystem,
            $fileStorageDb,
            $storeManager
        );
        $this->attributeValue = $attributeValue ?: ObjectManager::getInstance()->get(AttributeValue::class);
    }

    /**
     * @inheritdoc
     *
     * @since 101.0.0
     */
    protected function processDeletedImages($product, array &$images)
    {
        $filesToDelete = [];
        $recordsToDelete = [];
        $imagesToDelete = [];
        $imagesToNotDelete = [];
        foreach ($images as $image) {
            if (empty($image['removed'])) {
                $imagesToNotDelete[] = $image['file'];
            }
        }

        foreach ($images as $image) {
            if (!empty($image['removed'])) {
                if (!empty($image['value_id'])) {
                    $recordsToDelete[] = $image['value_id'];
                    if (!in_array($image['file'], $imagesToNotDelete)) {
                        $imagesToDelete[] = $image['file'];
                        if ($this->canDeleteImage($image['file'])) {
                            $filesToDelete[] = ltrim($image['file'], '/');
                        }
                    }
                }
            }
        }

        $this->deleteMediaAttributeValues($product, $imagesToDelete);
        $this->resourceModel->deleteGallery($recordsToDelete);
        $this->removeDeletedImages($filesToDelete);
    }

    /**
     * Check if image exists and is not used by any other products
     *
     * @param string $file
     * @return bool
     */
    private function canDeleteImage(string $file): bool
    {
        $catalogPath = $this->mediaConfig->getBaseMediaPath();
        $filePath = $this->mediaDirectory->getRelativePath($catalogPath . $file);
        return $this->mediaDirectory->isFile($filePath)
            && $this->resourceModel->countImageUses($file) <= 1;
    }

    /**
     * @inheritdoc
     *
     * @since 101.0.0
     */
    protected function processNewImage($product, array &$image)
    {
        $data = [];

        if (empty($image['value_id'])) {
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
        } elseif (!empty($image['recreate'])) {
            $data['value_id'] = $image['value_id'];
            $data['value'] = $image['file'];
            $data['attribute_id'] = $this->getAttribute()->getAttributeId();

            if (!empty($image['media_type'])) {
                $data['media_type'] = $image['media_type'];
            }

            $this->resourceModel->saveDataRow(Gallery::GALLERY_TABLE, $data);
        }

        return $data;
    }

    /**
     * Retrieve store ids from product.
     *
     * @param Product $product
     * @return array
     * @since 101.0.0
     */
    protected function extractStoreIds($product)
    {
        $storeIds = $product->getStoreIds();
        $storeIds[] = Store::DEFAULT_STORE_ID;

        // Removing current storeId.
        $storeIds = array_flip($storeIds);
        unset($storeIds[$product->getStoreId()]);
        $storeIds = array_keys($storeIds);

        return $storeIds;
    }

    /**
     * Remove deleted images.
     *
     * @param array $files
     * @return null
     * @since 101.0.0
     */
    protected function removeDeletedImages(array $files)
    {
        $catalogPath = $this->mediaConfig->getBaseMediaPath();

        foreach ($files as $filePath) {
            $this->mediaDirectory->delete($catalogPath . '/' . $filePath);
        }
        return null;
    }

    /**
     * Delete media attributes values for given images
     *
     * @param Product $product
     * @param string[] $images
     */
    private function deleteMediaAttributeValues(Product $product, array $images): void
    {
        if ($images) {
            $values = $this->attributeValue->getValues(
                ProductInterface::class,
                $product->getData($this->metadata->getLinkField()),
                $this->mediaConfig->getMediaAttributeCodes()
            );
            $valuesToDelete = [];
            foreach ($values as $value) {
                if (in_array($value['value'], $images, true)) {
                    $valuesToDelete[] = $value;
                }
            }
            if ($valuesToDelete) {
                $this->attributeValue->deleteValues(
                    ProductInterface::class,
                    $valuesToDelete
                );
            }
        }
    }
}
