<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $coreFileStorageDatabase = null;

    /**
     * Media Directory object (writable).
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Image constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->logger = $logger;
    }

    /**
     * Save uploaded file and set its name to category
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
     */
    public function afterSave($object)
    {
        $realPath = 'catalog/category/';
        $tmpPath = 'catalog/tmp/category/';

        $value = $object->getData($this->getAttribute()->getName() . '_additional_data');

        if (is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
            return $this;
        }

        if (isset($value[0]['name']) && isset($value[0]['tmp_name'])) {
            try {
                $tmpImagePath = rtrim($tmpPath, '/') . '/' . ltrim($value[0]['name'], '/');
                $newImagePath = rtrim($realPath, '/') . '/' . ltrim($value[0]['name'], '/');

                $this->coreFileStorageDatabase->copyFile(
                    $tmpImagePath,
                    $newImagePath
                );

                $this->mediaDirectory->renameFile(
                    $tmpImagePath,
                    $newImagePath
                );

                $object->setData($this->getAttribute()->getName(), $value[0]['name']);
                $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                    $this->logger->critical($e);
                }
            }
        }
        return $this;
    }
}
