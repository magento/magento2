<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Favicon;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Favicon implementation
 */
class Favicon implements \Magento\Framework\View\Page\FaviconInterface
{
    /**
     * @var string
     */
    protected $faviconFile;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $fileStorageDatabase;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDatabase
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->fileStorageDatabase = $fileStorageDatabase;
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * @return string
     */
    public function getFaviconFile()
    {
        if (null === $this->faviconFile) {
            $this->faviconFile = $this->prepareFaviconFile();
        }
        return $this->faviconFile;
    }

    /**
     * @return string
     */
    public function getDefaultFavicon()
    {
        return 'Magento_Theme::favicon.ico';
    }

    /**
     * @return string
     */
    protected function prepareFaviconFile()
    {
        $folderName = \Magento\Config\Model\Config\Backend\Image\Favicon::UPLOAD_DIR;
        $scopeConfig = $this->scopeConfig->getValue(
            'design/head/shortcut_icon',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $scopeConfig;
        $faviconUrl = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;

        if ($scopeConfig !== null && $this->checkIsFile($path)) {
            return $faviconUrl;
        }

        return false;
    }

    /**
     * If DB file storage is on - find there, otherwise - just file_exists
     *
     * @param string $filename relative file path
     * @return bool
     */
    protected function checkIsFile($filename)
    {
        if ($this->fileStorageDatabase->checkDbUsage() && !$this->mediaDirectory->isFile($filename)) {
            $this->fileStorageDatabase->saveFileToFilesystem($filename);
        }
        return $this->mediaDirectory->isFile($filename);
    }
}
