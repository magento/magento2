<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Favicon;

use Magento\Config\Model\Config\Backend\Image\Favicon as ImageFavicon;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\FaviconInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Favicon implementation
 */
class Favicon implements FaviconInterface
{
    /**
     * @var string
     */
    protected $faviconFile;

    /**
     * @var ReadInterface
     */
    protected $mediaDirectory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Database $fileStorageDatabase
     * @param Filesystem $filesystem
     */
    public function __construct(
        protected readonly StoreManagerInterface $storeManager,
        protected readonly ScopeConfigInterface $scopeConfig,
        protected readonly Database $fileStorageDatabase,
        Filesystem $filesystem
    ) {
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
        $folderName = ImageFavicon::UPLOAD_DIR;
        $scopeConfig = $this->scopeConfig->getValue(
            'design/head/shortcut_icon',
            ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $scopeConfig;
        $faviconUrl = $this->storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path;

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
