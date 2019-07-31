<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Attribute\Backend;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;

/**
 * Catalog category image attribute backend model
 *
 * @api
 * @since 100.0.2
 */
class Image extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     *
     * @deprecated 101.0.0
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     *
     * @deprecated 101.0.0
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     *
     * @deprecated 101.0.0
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     *
     * @deprecated 101.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * @var string
     */
    private $additionalData = '_additional_data_';

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
    }

    /**
     * Gets image name from $value array.
     *
     * Will return empty string in a case when $value is not an array.
     *
     * @param array $value Attribute value
     * @return string
     */
    private function getUploadedImageName($value)
    {
        if (is_array($value) && isset($value[0]['name'])) {
            return $value[0]['name'];
        }

        return '';
    }

    /**
     * Check that image name exists in catalog/category directory and return new image name if it already exists.
     *
     * @param string $imageName
     * @return string
     */
    private function checkUniqueImageName(string $imageName): string
    {
        $imageUploader = $this->getImageUploader();
        $mediaDirectory = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $imageAbsolutePath = $mediaDirectory->getAbsolutePath(
            $imageUploader->getBasePath() . DIRECTORY_SEPARATOR . $imageName
        );

        $imageName = Uploader::getNewFilename($imageAbsolutePath);

        return $imageName;
    }

    /**
     * Avoiding saving potential upload data to DB.
     *
     * Will set empty image attribute value if image was not uploaded.
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 101.0.8
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $value = $object->getData($attributeName);

        if ($this->fileResidesOutsideCategoryDir($value)) {
            // use relative path for image attribute so we know it's outside of category dir when we fetch it
            $value[0]['name'] = $value[0]['url'];
        }

        if ($imageName = $this->getUploadedImageName($value)) {
            $imageName = $this->checkUniqueImageName($imageName);
            $object->setData($this->additionalData . $attributeName, $value);
            $object->setData($attributeName, $imageName);
        } elseif (!is_string($value)) {
            $object->setData($attributeName, null);
        }

        return parent::beforeSave($object);
    }

    /**
     * Get Instance of Category Image Uploader.
     *
     * @return \Magento\Catalog\Model\ImageUploader
     *
     * @deprecated 101.0.0
     */
    private function getImageUploader()
    {
        if ($this->imageUploader === null) {
            $this->imageUploader = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\CategoryImageUpload::class);
        }

        return $this->imageUploader;
    }

    /**
     * Check if temporary file is available for new image upload.
     *
     * @param array $value
     * @return bool
     */
    private function isTmpFileAvailable($value)
    {
        return is_array($value) && isset($value[0]['tmp_name']);
    }

    /**
     * Check for file path resides outside of category media dir. The URL will be a path including pub/media if true
     *
     * @param array|null $value
     * @return bool
     */
    private function fileResidesOutsideCategoryDir($value)
    {
        if (!is_array($value) || !isset($value[0]['url'])) {
            return false;
        }

        $fileUrl = ltrim($value[0]['url'], '/');
        $baseMediaDir = $this->_filesystem->getUri(DirectoryList::MEDIA);

        if (!$baseMediaDir) {
            return false;
        }

        return strpos($fileUrl, $baseMediaDir) === 0;
    }

    /**
     * Save uploaded file and set its name to category
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->additionalData . $this->getAttribute()->getName());

        if ($this->isTmpFileAvailable($value) && $imageName = $this->getUploadedImageName($value)) {
            try {
                $this->getImageUploader()->moveFileFromTmp($imageName);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }

        return $this;
    }
}
