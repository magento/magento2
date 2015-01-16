<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Image;

/**
 * Theme Image Uploader
 */
class Uploader
{
    /**
     * Allowed file extensions to upload
     *
     * @var array
     */
    protected $_allowedExtensions = ['jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'];

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * Transfer adapter
     *
     * @var \Zend_File_Transfer_Adapter_Http
     */
    protected $_transferAdapter;

    /**
     * Uploader factory
     *
     * @var \Magento\Framework\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\HTTP\Adapter\FileTransferFactory $adapterFactory
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $adapterFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_transferAdapter = $adapterFactory->create();
        $this->_uploaderFactory = $uploaderFactory;
    }

    /**
     * Upload preview image
     *
     * @param string $scope the request key for file
     * @param string $destinationPath path to upload directory
     * @return bool
     * @throws \Magento\Framework\Exception
     */
    public function uploadPreviewImage($scope, $destinationPath)
    {
        if (!$this->_transferAdapter->isUploaded($scope)) {
            return false;
        }
        if (!$this->_transferAdapter->isValid($scope)) {
            throw new \Magento\Framework\Exception(__('Uploaded image is not valid'));
        }
        $upload = $this->_uploaderFactory->create(['fileId' => $scope]);
        $upload->setAllowCreateFolders(true);
        $upload->setAllowedExtensions($this->_allowedExtensions);
        $upload->setAllowRenameFiles(true);
        $upload->setFilesDispersion(false);

        if (!$upload->checkAllowedExtension($upload->getFileExtension())) {
            throw new \Magento\Framework\Exception(__('Invalid image file type.'));
        }
        if (!$upload->save($destinationPath)) {
            throw new \Magento\Framework\Exception(__('Image can not be saved.'));
        }
        return $destinationPath . '/' . $upload->getUploadedFileName();
    }
}
