<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Quick style file uploader
 */
class ImageUploader extends \Magento\Framework\Object
{
    /**
     * Quick style images path prefix
     */
    const PATH_PREFIX_QUICK_STYLE = 'quick_style_images';

    /**
     * Storage path
     *
     * @var string
     */
    protected $_storagePath;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * Allowed extensions
     *
     * @var string[]
     */
    protected $_allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];

    /**
     * Generic constructor of change instance
     *
     * @param \Magento\Core\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        array $data = []
    ) {
        $this->_uploaderFactory = $uploaderFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($data);
    }

    /**
     * Get storage folder
     *
     * @return string
     */
    public function getStoragePath()
    {
        if (null === $this->_storagePath) {
            $this->_storagePath = $this->_getTheme()->getCustomization()->getCustomizationPath() .
                '/' .
                self::PATH_PREFIX_QUICK_STYLE;
        }
        return $this->_storagePath;
    }

    /**
     * Set storage path
     *
     * @param string $path
     * @return $this
     */
    public function setStoragePath($path)
    {
        $this->_storagePath = $path;
        return $this;
    }

    /**
     * Get theme
     *
     * @return \Magento\Core\Model\Theme
     * @throws \InvalidArgumentException
     */
    protected function _getTheme()
    {
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->getTheme();
        if (!$theme->getId()) {
            throw new \InvalidArgumentException('Theme was not found.');
        }
        return $theme;
    }

    /**
     * Upload image file
     *
     * @param string $key
     * @return array
     */
    public function uploadFile($key)
    {
        $result = [];
        /** @var $uploader \Magento\Core\Model\File\Uploader */
        $uploader = $this->_uploaderFactory->create(['fileId' => $key]);
        $uploader->setAllowedExtensions($this->_allowedExtensions);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowCreateFolders(true);

        if (!$uploader->save($this->getStoragePath())) {
            /** @todo add translator */
            throw new \Magento\Framework\Model\Exception('Cannot upload file.');
        }
        $result['css_path'] = implode(
            '/',
            ['..', self::PATH_PREFIX_QUICK_STYLE, $uploader->getUploadedFileName()]
        );
        $result['name'] = $uploader->getUploadedFileName();
        return $result;
    }

    /**
     * Remove file
     *
     * @param string $file
     * @return $this
     */
    public function removeFile($file)
    {
        $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $path = $directory->getRelativePath($this->getStoragePath() . '/' . $file);
        if ($directory->isExist($path)) {
            $directory->delete($path);
        }

        return $this;
    }
}
