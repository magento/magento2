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
use Magento\Framework\Exception\LocalizedException;
use \Magento\Catalog\Model\Product\Attribute\Backend\Media\MediaGalleryEntryProcessorPool;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * Json Helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper = null;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
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
     * @var Media\MediaGalleryEntryProcessorPool
     */
    protected $mediaEntryProcessorPool;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Resource\ProductFactory $productFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $resourceProductAttribute
     * @param MediaGalleryEntryProcessorPool $mediaEntryProcessorPool
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\ProductFactory $productFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $resourceProductAttribute,
        MediaGalleryEntryProcessorPool $mediaEntryProcessorPool
    ) {
        $this->_productFactory = $productFactory;
        $this->_eventManager = $eventManager;
        $this->_fileStorageDb = $fileStorageDb;
        $this->jsonHelper = $jsonHelper;
        $this->_resourceModel = $resourceProductAttribute;
        $this->_mediaConfig = $mediaConfig;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaEntryProcessorPool = $mediaEntryProcessorPool;
    }

    /**
     * Load attribute data after product loaded
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    public function beforeLoad($object)
    {
        $this->mediaEntryProcessorPool->processBeforeLoad($object, $this->getAttribute());
    }

    /**
     * Load attribute data after product loaded
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    public function afterLoad($object)
    {
        $this->mediaEntryProcessorPool->processAfterLoad($object, $this->getAttribute());
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function beforeSave($object)
    {
        $this->mediaEntryProcessorPool->processBeforeSave($object, $this->getAttribute());
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function afterSave($object)
    {
        $this->mediaEntryProcessorPool->processAfterSave($object, $this->getAttribute());
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function beforeDelete($object)
    {
        $this->mediaEntryProcessorPool->processBeforeDelete($object, $this->getAttribute());
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function afterDelete($object)
    {
        $this->mediaEntryProcessorPool->processAfterDelete($object, $this->getAttribute());
    }

    /**
     * Validate media_gallery attribute data
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
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
                throw new LocalizedException(__('The value of attribute "%1" must be unique.', $label));
            }
        }

        return true;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
            throw new LocalizedException(__('The image does not exist.'));
        }

        $pathinfo = pathinfo($file);
        $imgExtensions = ['jpg', 'jpeg', 'gif', 'png'];
        if (!isset($pathinfo['extension']) || !in_array(strtolower($pathinfo['extension']), $imgExtensions)) {
            throw new LocalizedException(__('Please correct the image file type.'));
        }

        $fileName = \Magento\MediaStorage\Model\File\Uploader::getCorrectFileName($pathinfo['basename']);
        $dispretionPath = \Magento\MediaStorage\Model\File\Uploader::getDispretionPath($fileName);
        $fileName = $dispretionPath . '/' . $fileName;

        $fileName = $this->_getNotDuplicatedFilename($fileName, $dispretionPath);

        $destinationFile = $this->_mediaConfig->getTmpMediaPath($fileName);

        try {
            /** @var $storageHelper \Magento\MediaStorage\Helper\File\Storage\Database */
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
            throw new LocalizedException(__('We couldn\'t move this file: %1.', $e->getMessage()));
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

        if ($mediaAttribute !== null) {
            $this->setMediaAttribute($product, $mediaAttribute, $fileName);
        }

        return $fileName;
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
     * @param string $file
     * @return string
     */
    protected function getFilenameFromTmp($file)
    {
        return strrpos($file, '.tmp') == strlen($file) - 4 ? substr($file, 0, strlen($file) - 4) : $file;
    }

    /**
     * Duplicate temporary images
     *
     * @param string $file
     * @return string
     */
    public function duplicateImageFromTmp($file)
    {
        $file = $this->getFilenameFromTmp($file);

        $destinationFile = $this->_getUniqueFileName($file, true);
        if ($this->_fileStorageDb->checkDbUsage()) {
            $this->_fileStorageDb->copyFile(
                $this->_mediaDirectory->getAbsolutePath($this->_mediaConfig->getTmpMediaShortUrl($file)),
                $this->_mediaConfig->getTmpMediaShortUrl($destinationFile)
            );
        } else {
            $this->_mediaDirectory->copyFile(
                $this->_mediaConfig->getTmpMediaPath($file),
                $this->_mediaConfig->getTmpMediaPath($destinationFile)
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
    protected function _getUniqueFileName($file, $forTmp = false)
    {
        if ($this->_fileStorageDb->checkDbUsage()) {
            $destFile = $this->_fileStorageDb->getUniqueFilename(
                $this->_mediaConfig->getBaseMediaUrlAddition(),
                $file
            );
        } else {
            $destinationFile = $forTmp
                ? $this->_mediaDirectory->getAbsolutePath($this->_mediaConfig->getTmpMediaPath($file))
                : $this->_mediaDirectory->getAbsolutePath($this->_mediaConfig->getMediaPath($file));
            $destFile = dirname(
                $file
            ) . '/' . \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
                $destinationFile
            );
        }

        return $destFile;
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
        $fileMediaName = $dispretionPath . '/' . \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
            $this->_mediaConfig->getMediaPath($fileName)
        );
        $fileTmpMediaName = $dispretionPath . '/' . \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
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
            if (empty($value['value_id'])) {
                continue;
            }
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
