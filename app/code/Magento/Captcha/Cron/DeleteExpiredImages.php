<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Cron;

use Magento\Framework\Filesystem\Io\File;
use Magento\Captcha\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Store\Model\StoreManager;

/**
 * Captcha cron actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteExpiredImages
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;

    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Adminhtml\Data
     */
    protected $_adminHelper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_fileInfo;

    /**
     * @var TargetDirectory
     */
    private $targetDirectory;

    /**
     * @param Data $helper
     * @param \Magento\Captcha\Helper\Adminhtml\Data $adminHelper
     * @param Filesystem $filesystem
     * @param StoreManager $storeManager
     * @param File $fileInfo
     * @param TargetDirectory|null $targetDirectory
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Captcha\Helper\Adminhtml\Data $adminHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\Filesystem\Io\File $fileInfo,
        ?TargetDirectory $targetDirectory = null
    ) {
        $this->_helper = $helper;
        $this->_adminHelper = $adminHelper;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA, DriverPool::FILE);
        $this->_storeManager = $storeManager;
        $this->_fileInfo = $fileInfo;
        $this->targetDirectory = $targetDirectory ?: ObjectManager::getInstance()->get(TargetDirectory::class);
    }

    /**
     * Delete Expired Captcha Images
     *
     * @return \Magento\Captcha\Cron\DeleteExpiredImages
     * @throws FileSystemException
     */
    public function execute()
    {
        foreach ($this->_storeManager->getWebsites() as $website) {
            $this->_deleteExpiredImagesForWebsite($this->_helper, $website, $website->getDefaultStore());
        }
        $this->_deleteExpiredImagesForWebsite($this->_adminHelper);

        return $this;
    }

    /**
     * Delete Expired Captcha Images for specific website
     *
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Store\Model\Website|null $website
     * @param \Magento\Store\Model\Store|null $store
     * @return void
     * @throws FileSystemException
     */
    protected function _deleteExpiredImagesForWebsite(
        \Magento\Captcha\Helper\Data $helper,
        ?\Magento\Store\Model\Website $website = null,
        ?\Magento\Store\Model\Store $store = null
    ) {
        $expire = time() - (int)$helper->getConfig('timeout', $store) * 60;
        $imageDirectory = $this->_mediaDirectory->getRelativePath($helper->getImgDir($website));
        // Images are generated locally, but stored in the configured media storage, which may be remote.
        $mediaDirectory = $this->targetDirectory->getDirectoryWrite(DirectoryList::MEDIA);
        foreach ($mediaDirectory->read($imageDirectory) as $filePath) {
            if ($mediaDirectory->isFile($filePath)
                && $this->_fileInfo->getPathInfo($filePath)['extension'] === 'png'
                && $mediaDirectory->stat($filePath)['mtime'] < $expire
            ) {
                $mediaDirectory->delete($filePath);
            }
        }
    }
}
