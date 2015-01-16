<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product media gallery attribute backend model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\Exception;

class Media extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var array
     */
    protected $_renamedImages = [];

    /**
     * Resource model
     *
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_fileStorageDb = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\Resource\ProductFactory
     */
    protected $_productFactory;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Resource\ProductFactory $productFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $resourceProductAttribute
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\ProductFactory $productFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $resourceProductAttribute
    ) {
        $this->_productFactory = $productFactory;
        $this->_eventManager = $eventManager;
        $this->_fileStorageDb = $fileStorageDb;
        $this->_coreData = $coreData;
        $this->_resourceModel = $resourceProductAttribute;
        $this->_mediaConfig = $mediaConfig;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Load attribute data after product loaded
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    public function afterLoad($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = [];
        $value['images'] = [];
        $value['values'] = [];
        $localAttributes = ['label', 'position', 'disabled'];

        foreach ($this->_getResource()->loadGallery($object, $this) as $image) {
            foreach ($localAttributes as $localAttribute) {
                if (is_null($image[$localAttribute])) {
                    $image[$localAttribute] = $this->_getDefaultValue($localAttribute, $image);
                }
            }
            $value['images'][] = $image;
        }

        $object->setData($attrCode, $value);
        return $this;
    }

    /**
     * @param string $key
     * @param string[] &$image
     * @return string
     */
    protected function _getDefaultValue($key, &$image)
    {
        if (isset($image[$key . '_default'])) {
            return $image[$key . '_default'];
        }

        return '';
    }

    /**
     * Validate media_gallery attribute data
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return bool
     * @throws Exception
     */
    public function validate($object)
    {
        if ($this->getAttribute()->getIsRequired()) {
            $value = $object->getData($this->getAttribute()->getAttributeCode());
            if ($this->getAttribute()->isValueEmpty($value)) {
                return false;
            }
        }
        if ($this->getAttribute()->getIsUnique()) {
            if (!$this->getAttribute()->getEntity()->checkAttributeUniqueValue($this->getAttribute(), $object)) {
                $label = $this->getAttribute()->getFrontend()->getLabel();
                throw new Exception(__('The value of attribute "%1" must be unique.', $label));
            }
        }

        return true;
    }

    /**
     * @param \Magento\Framework\Object $object
     * @return $this|void
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!is_array($value) || !isset($value['images'])) {
            return;
        }

        if (!is_array($value['images']) && strlen($value['images']) > 0) {
            $value['images'] = $this->_coreData->jsonDecode($value['images']);
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
                    $newFile = $this->_moveImageFromTmp($image['file']);
                    $image['new_file'] = $newFile;
                    $newImages[$image['file']] = $image;
                    $this->_renamedImages[$image['file']] = $newFile;
                    $image['file'] = $newFile;
                } else {
                    $existImages[$image['file']] = $image;
                }
            }
        } else {
            // For duplicating we need copy original images.
            $duplicate = [];
            foreach ($value['images'] as &$image) {
                if (empty($image['value_id'])) {
                    continue;
                }
                $duplicate[$image['value_id']] = $this->_copyImage($image['file']);
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

        return $this;
    }

    /**
     * Retrieve renamed image name
     *
     * @param string $file
     * @return string
     */
    public function getRenamedImage($file)
    {
        if (isset($this->_renamedImages[$file])) {
            return $this->_renamedImages[$file];
        }

        return $file;
    }

    /**
     * @param \Magento\Framework\Object $object
     * @return void
     */
    public function afterSave($object)
    {
        if ($object->getIsDuplicate() == true) {
            $this->duplicate($object);
            return;
        }

        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!is_array($value) || !isset($value['images']) || $object->isLockedAttribute($attrCode)) {
            return;
        }

        $storeId = $object->getStoreId();

        $storeIds = $object->getStoreIds();
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        // remove current storeId
        $storeIds = array_flip($storeIds);
        unset($storeIds[$storeId]);
        $storeIds = array_keys($storeIds);

        $images = $this->_productFactory->create()->getAssignedImages($object, $storeIds);

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
                $data['entity_id'] = $object->getId();
                $data['attribute_id'] = $this->getAttribute()->getId();
                $data['value'] = $image['file'];
                $image['value_id'] = $this->_getResource()->insertGallery($data);
            }

            $this->_getResource()->deleteGalleryValueInStore($image['value_id'], $object->getStoreId());

            // Add per store labels, position, disabled
            $data = [];
            $data['value_id'] = $image['value_id'];

            $data['label'] = isset($image['label']) ? $image['label'] : '';
            $data['position'] = isset($image['position']) ? (int)$image['position'] : 0;
            $data['disabled'] = isset($image['disabled']) ? (int)$image['disabled'] : 0;
            $data['store_id'] = (int)$object->getStoreId();
            $data['entity_id'] = (int)$object->getId();

            $this->_getResource()->insertGalleryValueInStore($data);
        }

        $this->_getResource()->deleteGallery($recordsToDelete);
        $this->removeDeletedImages($filesToDelete);
        $object->setData($attrCode, $value);
    }

    /**
     * @param array $files
     * @return null
     */
    protected function removeDeletedImages(array $files)
    {
        $catalogPath = $this->_mediaConfig->getBaseMediaPath();
        foreach ($files as $filePath) {
            $this->_mediaDirectory->delete($catalogPath . '/' . $filePath);
        }
    }

    /**
     * Add image to media gallery and return new filename
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $file file path of image in file system
     * @param string|string[] $mediaAttribute code of attribute with type 'media_image',
     *                                                      leave blank if image should be only in gallery
     * @param boolean $move if true, it will move source file
     * @param boolean $exclude mark image as disabled in product page view
     * @return string
     * @throws Exception
     */
    public function addImage(
        \Magento\Catalog\Model\Product $product,
        $file,
        $mediaAttribute = null,
        $move = false,
        $exclude = true
    ) {
        $file = $this->_mediaDirectory->getRelativePath($file);
        if (!$this->_mediaDirectory->isFile($file)) {
            throw new Exception(__('The image does not exist.'));
        }

        $pathinfo = pathinfo($file);
        $imgExtensions = ['jpg', 'jpeg', 'gif', 'png'];
        if (!isset($pathinfo['extension']) || !in_array(strtolower($pathinfo['extension']), $imgExtensions)) {
            throw new Exception(__('Please correct the image file type.'));
        }

        $fileName = \Magento\Core\Model\File\Uploader::getCorrectFileName($pathinfo['basename']);
        $dispretionPath = \Magento\Core\Model\File\Uploader::getDispretionPath($fileName);
        $fileName = $dispretionPath . '/' . $fileName;

        $fileName = $this->_getNotDuplicatedFilename($fileName, $dispretionPath);

        $destinationFile = $this->_mediaConfig->getTmpMediaPath($fileName);

        try {
            /** @var $storageHelper \Magento\Core\Helper\File\Storage\Database */
            $storageHelper = $this->_fileStorageDb;
            if ($move) {
                $this->_mediaDirectory->renameFile($file, $destinationFile);

                //If this is used, filesystem should be configured properly
                $storageHelper->saveFile($this->_mediaConfig->getTmpMediaShortUrl($fileName));
            } else {
                $this->_mediaDirectory->copyFile($file, $destinationFile);

                $storageHelper->saveFile($this->_mediaConfig->getTmpMediaShortUrl($fileName));
                $this->_mediaDirectory->changePermissions($destinationFile, 0777);
            }
        } catch (\Exception $e) {
            throw new Exception(__('We couldn\'t move this file: %1.', $e->getMessage()));
        }

        $fileName = str_replace('\\', '/', $fileName);

        $attrCode = $this->getAttribute()->getAttributeCode();
        $mediaGalleryData = $product->getData($attrCode);
        $position = 0;
        if (!is_array($mediaGalleryData)) {
            $mediaGalleryData = ['images' => []];
        }

        foreach ($mediaGalleryData['images'] as &$image) {
            if (isset($image['position']) && $image['position'] > $position) {
                $position = $image['position'];
            }
        }

        $position++;
        $mediaGalleryData['images'][] = [
            'file' => $fileName,
            'position' => $position,
            'label' => '',
            'disabled' => (int)$exclude,
        ];

        $product->setData($attrCode, $mediaGalleryData);

        if (!is_null($mediaAttribute)) {
            $this->setMediaAttribute($product, $mediaAttribute, $fileName);
        }

        return $fileName;
    }

    /**
     * Add images with different media attributes.
     * Image will be added only once if the same image is used with different media attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $fileAndAttributesArray array of arrays of filename and corresponding media attribute
     * @param string $filePath path, where image cand be found
     * @param boolean $move if true, it will move source file
     * @param boolean $exclude mark image as disabled in product page view
     * @return array array of parallel arrays with original and renamed files
     */
    public function addImagesWithDifferentMediaAttributes(
        \Magento\Catalog\Model\Product $product,
        $fileAndAttributesArray,
        $filePath = '',
        $move = false,
        $exclude = true
    ) {
        $alreadyAddedFiles = [];
        $alreadyAddedFilesNames = [];

        foreach ($fileAndAttributesArray as $key => $value) {
            $keyInAddedFiles = array_search($value['file'], $alreadyAddedFiles, true);
            if ($keyInAddedFiles === false) {
                $savedFileName = $this->addImage($product, $filePath . $value['file'], null, $move, $exclude);
                $alreadyAddedFiles[$key] = $value['file'];
                $alreadyAddedFilesNames[$key] = $savedFileName;
            } else {
                $savedFileName = $alreadyAddedFilesNames[$keyInAddedFiles];
            }

            if (!is_null($value['mediaAttribute'])) {
                $this->setMediaAttribute($product, $value['mediaAttribute'], $savedFileName);
            }
        }

        return ['alreadyAddedFiles' => $alreadyAddedFiles, 'alreadyAddedFilesNames' => $alreadyAddedFilesNames];
    }

    /**
     * Update image in gallery
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $file
     * @param array $data
     * @return $this
     */
    public function updateImage(\Magento\Catalog\Model\Product $product, $file, $data)
    {
        $fieldsMap = [
            'label' => 'label',
            'position' => 'position',
            'disabled' => 'disabled',
            'exclude' => 'disabled',
        ];

        $attrCode = $this->getAttribute()->getAttributeCode();

        $mediaGalleryData = $product->getData($attrCode);

        if (!isset($mediaGalleryData['images']) || !is_array($mediaGalleryData['images'])) {
            return $this;
        }

        foreach ($mediaGalleryData['images'] as &$image) {
            if ($image['file'] == $file) {
                foreach ($fieldsMap as $mappedField => $realField) {
                    if (isset($data[$mappedField])) {
                        $image[$realField] = $data[$mappedField];
                    }
                }
            }
        }

        $product->setData($attrCode, $mediaGalleryData);
        return $this;
    }

    /**
     * Remove image from gallery
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $file
     * @return $this
     */
    public function removeImage(\Magento\Catalog\Model\Product $product, $file)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();

        $mediaGalleryData = $product->getData($attrCode);

        if (!isset($mediaGalleryData['images']) || !is_array($mediaGalleryData['images'])) {
            return $this;
        }

        foreach ($mediaGalleryData['images'] as &$image) {
            if ($image['file'] == $file) {
                $image['removed'] = 1;
            }
        }

        $product->setData($attrCode, $mediaGalleryData);

        return $this;
    }

    /**
     * Retrieve image from gallery
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $file
     * @return array|boolean
     */
    public function getImage(\Magento\Catalog\Model\Product $product, $file)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $mediaGalleryData = $product->getData($attrCode);
        if (!isset($mediaGalleryData['images']) || !is_array($mediaGalleryData['images'])) {
            return false;
        }

        foreach ($mediaGalleryData['images'] as $image) {
            if ($image['file'] == $file) {
                return $image;
            }
        }

        return false;
    }

    /**
     * Clear media attribute value
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string|string[] $mediaAttribute
     * @return $this
     */
    public function clearMediaAttribute(\Magento\Catalog\Model\Product $product, $mediaAttribute)
    {
        $mediaAttributeCodes = array_keys($product->getMediaAttributes());

        if (is_array($mediaAttribute)) {
            foreach ($mediaAttribute as $atttribute) {
                if (in_array($atttribute, $mediaAttributeCodes)) {
                    $product->setData($atttribute, null);
                }
            }
        } elseif (in_array($mediaAttribute, $mediaAttributeCodes)) {
            $product->setData($mediaAttribute, null);
        }

        return $this;
    }

    /**
     * Set media attribute value
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string|string[] $mediaAttribute
     * @param string $value
     * @return $this
     */
    public function setMediaAttribute(\Magento\Catalog\Model\Product $product, $mediaAttribute, $value)
    {
        $mediaAttributeCodes = array_keys($product->getMediaAttributes());

        if (is_array($mediaAttribute)) {
            foreach ($mediaAttribute as $atttribute) {
                if (in_array($atttribute, $mediaAttributeCodes)) {
                    $product->setData($atttribute, $value);
                }
            }
        } elseif (in_array($mediaAttribute, $mediaAttributeCodes)) {
            $product->setData($mediaAttribute, $value);
        }

        return $this;
    }

    /**
     * Retrieve resource model
     *
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media
     */
    protected function _getResource()
    {
        return $this->_resourceModel;
    }

    /**
     * Move image from temporary directory to normal
     *
     * @param string $file
     * @return string
     */
    protected function _moveImageFromTmp($file)
    {
        if (strrpos($file, '.tmp') == strlen($file) - 4) {
            $file = substr($file, 0, strlen($file) - 4);
        }
        $destinationFile = $this->_getUniqueFileName($file);

        /** @var $storageHelper \Magento\Core\Helper\File\Storage\Database */
        $storageHelper = $this->_fileStorageDb;

        if ($storageHelper->checkDbUsage()) {
            $storageHelper->renameFile(
                $this->_mediaConfig->getTmpMediaShortUrl($file),
                $this->_mediaConfig->getMediaShortUrl($destinationFile)
            );

            $this->_mediaDirectory->delete($this->_mediaConfig->getTmpMediaPath($file));
            $this->_mediaDirectory->delete($this->_mediaConfig->getMediaPath($destinationFile));
        } else {
            $this->_mediaDirectory->renameFile(
                $this->_mediaConfig->getTmpMediaPath($file),
                $this->_mediaConfig->getMediaPath($destinationFile)
            );
        }

        return str_replace('\\', '/', $destinationFile);
    }

    /**
     * Check whether file to move exists. Getting unique name
     *
     * @param <type> $file
     * @return string
     */
    protected function _getUniqueFileName($file)
    {
        if ($this->_fileStorageDb->checkDbUsage()) {
            $destFile = $this->_fileStorageDb->getUniqueFilename(
                $this->_mediaConfig->getBaseMediaUrlAddition(),
                $file
            );
        } else {
            $destFile = dirname(
                $file
            ) . '/' . \Magento\Core\Model\File\Uploader::getNewFileName(
                $this->_mediaDirectory->getAbsolutePath($this->_mediaConfig->getMediaPath($file))
            );
        }

        return $destFile;
    }

    /**
     * Copy image and return new filename.
     *
     * @param string $file
     * @return string
     * @throws Exception
     */
    protected function _copyImage($file)
    {
        try {
            $destinationFile = $this->_getUniqueFileName($file);

            if (!$this->_mediaDirectory->isFile($this->_mediaConfig->getMediaPath($file))) {
                throw new \Exception();
            }

            if ($this->_fileStorageDb->checkDbUsage()) {
                $this->_fileStorageDb->copyFile(
                    $this->_mediaDirectory->getAbsolutePath($this->_mediaConfig->getMediaShortUrl($file)),
                    $this->_mediaConfig->getMediaShortUrl($destinationFile)
                );
                $this->_mediaDirectory->delete($this->_mediaConfig->getMediaPath($destinationFile));
            } else {
                $this->_mediaDirectory->copyFile(
                    $this->_mediaConfig->getMediaPath($file),
                    $this->_mediaConfig->getMediaPath($destinationFile)
                );
            }

            return str_replace('\\', '/', $destinationFile);
        } catch (\Exception $e) {
            $file = $this->_mediaConfig->getMediaPath($file);
            throw new Exception(
                __('We couldn\'t copy file %1. Please delete media with non-existing images and try again.', $file)
            );
        }
    }

    /**
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function duplicate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $mediaGalleryData = $object->getData($attrCode);

        if (!isset($mediaGalleryData['images']) || !is_array($mediaGalleryData['images'])) {
            return $this;
        }

        $this->_getResource()->duplicate(
            $this,
            isset($mediaGalleryData['duplicate']) ? $mediaGalleryData['duplicate'] : [],
            $object->getOriginalId(),
            $object->getId()
        );

        return $this;
    }

    /**
     * Get filename which is not duplicated with other files in media temporary and media directories
     *
     * @param string $fileName
     * @param string $dispretionPath
     * @return string
     */
    protected function _getNotDuplicatedFilename($fileName, $dispretionPath)
    {
        $fileMediaName = $dispretionPath . '/' . \Magento\Core\Model\File\Uploader::getNewFileName(
            $this->_mediaConfig->getMediaPath($fileName)
        );
        $fileTmpMediaName = $dispretionPath . '/' . \Magento\Core\Model\File\Uploader::getNewFileName(
            $this->_mediaConfig->getTmpMediaPath($fileName)
        );

        if ($fileMediaName != $fileTmpMediaName) {
            if ($fileMediaName != $fileName) {
                return $this->_getNotDuplicatedFileName($fileMediaName, $dispretionPath);
            } elseif ($fileTmpMediaName != $fileName) {
                return $this->_getNotDuplicatedFilename($fileTmpMediaName, $dispretionPath);
            }
        }

        return $fileMediaName;
    }

    /**
     * Retrieve data for update attribute
     *
     * @param  \Magento\Catalog\Model\Product $object
     * @return array
     */
    public function getAffectedFields($object)
    {
        $data = [];
        $images = (array)$object->getData($this->getAttribute()->getName());
        $tableName = $this->_getResource()->getMainTable();
        foreach ($images['images'] as $value) {
            $data[$tableName][] = [
                'attribute_id' => $this->getAttribute()->getAttributeId(),
                'value_id' => $value['value_id'],
                'entity_id' => $object->getId(),
            ];
        }
        return $data;
    }

    /**
     * Attribute value is not to be saved in a conventional way, separate table is used to store the complex value
     *
     * {@inheritdoc}
     */
    public function isScalar()
    {
        return false;
    }
}
