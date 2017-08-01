<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Product image attribute backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Image extends AbstractBackend
{
    /**
     * Filesystem facade
     *
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     * @since 2.0.0
     */
    protected $_fileUploaderFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }

    /**
     * After save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this|void
     * @since 2.0.0
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());

        if (is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
            return;
        }

        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $this->getAttribute()->getName()]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
        } catch (\Exception $e) {
            return $this;
        }
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'catalog/product/'
        );
        $uploader->save($path);

        $fileName = $uploader->getUploadedFileName();
        if ($fileName) {
            $object->setData($this->getAttribute()->getName(), $fileName);
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        }
        return $this;
    }
}
