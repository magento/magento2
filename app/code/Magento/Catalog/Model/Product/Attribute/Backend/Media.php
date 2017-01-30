<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Media backend model
 */
class Media extends Product\Attribute\Backend\AbstractMedia
{
    /**
     * @param Product $object
     * @return Product
     */
    public function afterLoad($object)
    {
        $mediaEntries = $this->getResource()->loadProductGalleryByAttributeId(
            $object,
            $this->getAttribute()->getId()
        );
        $this->addMediaDataToProduct(
            $object,
            $mediaEntries
        );

        return $object;
    }

    /**
     * @param Product $product
     * @param array $mediaEntries
     * @return $this
     */
    public function addMediaDataToProduct(Product $product, array $mediaEntries)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = [];
        $value['images'] = [];
        $value['values'] = [];

        foreach ($mediaEntries as $mediaEntry) {
            $mediaEntry = $this->substituteNullsWithDefaultValues($mediaEntry);
            $value['images'][] = $mediaEntry;
        }
        $product->setData($attrCode, $value);

        return $this;
    }

    /**
     * @param array $rawData
     * @return array
     */
    private function substituteNullsWithDefaultValues(array $rawData)
    {
        $processedData = [];
        foreach ($rawData as $key => $rawValue) {
            if (null !== $rawValue) {
                $processedValue = $rawValue;
            } elseif (isset($rawData[$key . '_default'])) {
                $processedValue = $rawData[$key . '_default'];
            } else {
                $processedValue = null;
            }
            $processedData[$key] = $processedValue;
        }

        return $processedData;
    }

    /**
     * @param Product $object
     * @return Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!is_array($value) || !isset($value['images'])) {
            return $object;
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
        if ($object->getIsDuplicate() != true) {
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

        foreach ($object->getMediaAttributes() as $mediaAttribute) {
            $mediaAttrCode = $mediaAttribute->getAttributeCode();
            $attrData = $object->getData($mediaAttrCode);

            if (in_array($attrData, $clearImages)) {
                $object->setData($mediaAttrCode, 'no_selection');
            }

            if (in_array($attrData, array_keys($newImages))) {
                $object->setData($mediaAttrCode, $newImages[$attrData]['new_file']);
                $object->setData($mediaAttrCode . '_label', $newImages[$attrData]['label']);
            }

            if (in_array($attrData, array_keys($existImages))) {
                $object->setData($mediaAttrCode . '_label', $existImages[$attrData]['label']);
            }
        }

        $object->setData($attrCode, $value);

        return $object;
    }

    /**
     * @param Product $object
     * @return Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterSave($object)
    {
        if ($object->getIsDuplicate() == true) {
            $this->duplicate($object, $this->getAttribute());
            return $object;
        }

        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!is_array($value) || !isset($value['images']) || $object->isLockedAttribute($attrCode)) {
            return $object;
        }

        $storeId = $object->getStoreId();

        $storeIds = $object->getStoreIds();
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        // remove current storeId
        $storeIds = array_flip($storeIds);
        unset($storeIds[$storeId]);
        $storeIds = array_keys($storeIds);

        $images = $this->productFactory->create()->getAssignedImages($object, $storeIds);

        $picturesInOtherStores = [];
        foreach ($images as $image) {
            $picturesInOtherStores[$image['filepath']] = true;
        }

        $recordsToDelete = [];
        $filesToDelete = [];
        foreach ($value['images'] as &$image) {
            if (!empty($image['removed'])) {
                if (!empty($image['value_id']) && !isset($picturesInOtherStores[$image['file']])) {
                    $recordsToDelete[] = $image['value_id'];
                    $filesToDelete[] = ltrim($image['file'], '/');
                }
                continue;
            }

            if (empty($image['value_id'])) {
                $data = [];
                $data['attribute_id'] = $this->getAttribute()->getId();
                $data['value'] = $image['file'];
                if (!empty($image['media_type'])) {
                    $data['media_type'] = $image['media_type'];
                }
                $image['value_id'] = $this->getResource()->insertGallery($data);
                $this->getResource()->bindValueToEntity($image['value_id'], $object->getId());
            }

            $this->getResource()->deleteGalleryValueInStore(
                $image['value_id'],
                $object->getId(),
                $object->getStoreId()
            );

            // Add per store labels, position, disabled
            $data = [];
            $data['value_id'] = $image['value_id'];
            $data['entity_id'] = $object->getId();

            $data['label'] = isset($image['label']) ? $image['label'] : '';
            $data['position'] = isset($image['position']) ? (int)$image['position'] : 0;
            $data['disabled'] = isset($image['disabled']) ? (int)$image['disabled'] : 0;
            $data['store_id'] = (int)$object->getStoreId();
            $data['entity_id'] = (int)$object->getId();

            $this->getResource()->insertGalleryValueInStore($data);
        }

        $this->getResource()->deleteGallery($recordsToDelete);
        $this->removeDeletedImages($filesToDelete);
        $object->setData($attrCode, $value);

        return $object;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param AbstractAttribute $attribute
     * @return $this
     */
    protected function duplicate($product, $attribute)
    {
        $mediaGalleryData = $product->getData($attribute->getAttributeCode());

        if (!isset($mediaGalleryData['images']) || !is_array($mediaGalleryData['images'])) {
            return $this;
        }

        $this->getResource()->duplicate(
            $attribute->getId(),
            isset($mediaGalleryData['duplicate']) ? $mediaGalleryData['duplicate'] : [],
            $product->getOriginalId(),
            $product->getId()
        );

        return $this;
    }

    /**
     * @param array $files
     * @return null
     */
    protected function removeDeletedImages(array $files)
    {
        $catalogPath = $this->mediaConfig->getBaseMediaPath();
        foreach ($files as $filePath) {
            $this->mediaDirectory->delete($catalogPath . '/' . $filePath);
        }
    }

    /**
     * Move image from temporary directory to normal
     *
     * @param string $file
     * @return string
     */
    protected function moveImageFromTmp($file)
    {
        $file = $this->getFilenameFromTmp($file);
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
     * @param string $file
     * @return string
     */
    protected function getFilenameFromTmp($file)
    {
        return strrpos($file, '.tmp') == strlen($file) - 4 ? substr($file, 0, strlen($file) - 4) : $file;
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
            throw new LocalizedException(
                __('We couldn\'t copy file %1. Please delete media with non-existing images and try again.', $file)
            );
        }
    }

    /**
     * @deprecated
     * @param string $key
     * @param string[] &$image
     * @return string
     */
    protected function findDefaultValue($key, &$image)
    {
        if (isset($image[$key . '_default'])) {
            return $image[$key . '_default'];
        }

        return '';
    }
}
