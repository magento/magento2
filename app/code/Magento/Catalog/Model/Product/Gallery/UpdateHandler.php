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
        $picturesInOtherStores = [];
        $imagesToDelete = [];

        foreach ($this->resourceModel->getProductImages($product, $this->extractStoreIds($product)) as $image) {
            $picturesInOtherStores[$image['filepath']] = true;
        }

        foreach ($images as &$image) {
            if (!empty($image['removed'])) {
                if (!empty($image['value_id'])) {
                    if (preg_match('/\.\.(\\\|\/)/', $image['file'])) {
                        continue;
                    }
                    $recordsToDelete[] = $image['value_id'];
                    $imagesToDelete[] = $image['file'];
                    $catalogPath = $this->mediaConfig->getBaseMediaPath();
                    $isFile = $this->mediaDirectory->isFile($catalogPath . $image['file']);
                    // only delete physical files if they are not used by any other products and if this file exist
                    if ($isFile && !($this->resourceModel->countImageUses($image['file']) > 1)) {
                        $filesToDelete[] = ltrim($image['file'], '/');
                    }
                }
            }
        }

        $this->deleteMediaAttributeValues($product, $imagesToDelete);
        $this->resourceModel->deleteGallery($recordsToDelete);
        $this->removeDeletedImages($filesToDelete);
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
