<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog category image attribute backend model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Category\Attribute\Backend;

class Image extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    const ADDITIONAL_DATA_SUFFIX = '_additional_data';

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     *
     * @deprecated
     */
    protected $_uploaderFactory;

    /**
     * Filesystem facade
     *
     * @var \Magento\Framework\Filesystem
     *
     * @deprecated
     */
    protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     *
     * @deprecated
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     *
     * @deprecated
     */
    protected $_logger;

    /**
     * @var array
     */
    protected $actions = ['delete'];

    /**
     * Image uploader
     *
     * @var \Magento\Catalog\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * Image constructor.
     *
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
     * Check if there is image upload data available for current attribute
     *
     * @return bool
     */
    protected function _hasUploadedImage($value)
    {
        if (!is_array($value)) {
            return false;
        }

        if (!count($value)) {
            return false;
        }

        $imageData = reset($value);

        return isset($imageData['name']) && isset($imageData['tmp_name']);
    }

    protected function _getUploadedImageName($value)
    {
        if (!$this->_hasUploadedImage($value)) {
            return '';
        }

        $imageData = reset($value);

        return $imageData['name'];
    }

    /**
     * Check if image attribute value has any action directives to perform after the save
     *
     * @param $object
     * @return bool
     */
    protected function _hasImageActions($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $value = $object->getData($attributeName);

        return is_array($value) && array_intersect(array_keys($value), $this->actions);
    }

    /**
     * Before save method. Avoiding saving potential upload data to DB
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $value = $object->getData($attributeName);

        if ($this->_hasUploadedImage($value)) {
            $imageName = $this->_getUploadedImageName($value);

            $object->setData($attributeName . self::ADDITIONAL_DATA_SUFFIX, $value);
            $object->setData($attributeName, $imageName);
        } else if ($this->_hasImageActions($object)) {
            $object->setData($attributeName, '');
        }

        return parent::beforeSave($object);
    }

    /**
     * Get image uploader
     *
     * @return \Magento\Catalog\Model\ImageUploader
     *
     * @deprecated
     */
    private function getImageUploader()
    {
        if ($this->imageUploader === null) {
            $this->imageUploader = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Catalog\CategoryImageUpload'
            );
        }
        return $this->imageUploader;
    }

    /**
     * Save uploaded file and set its name to category
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName() . self::ADDITIONAL_DATA_SUFFIX);

        if (!$this->_hasUploadedImage($value)) {
            return $this;
        }

        $imageName = $this->_getUploadedImageName($value);

        try {
            $this->getImageUploader()->moveFileFromTmp($imageName);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return $this;
    }
}
