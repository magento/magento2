<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Captcha cron actions
 */
class Cron
{
    /**
     * CAPTCHA helper
     *
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
     * @var \Magento\Captcha\Model\Resource\LogFactory
     */
    protected $_resLogFactory;

    /**
     * @param Resource\LogFactory $resLogFactory
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Captcha\Helper\Adminhtml\Data $adminHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManager $storeManager
     */
    public function __construct(
        Resource\LogFactory $resLogFactory,
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Captcha\Helper\Adminhtml\Data $adminHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManager $storeManager
    ) {
        $this->_resLogFactory = $resLogFactory;
        $this->_helper = $helper;
        $this->_adminHelper = $adminHelper;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
    }

    /**
     * Delete Unnecessary logged attempts
     *
     * @return \Magento\Captcha\Model\Observer
     */
    public function deleteOldAttempts()
    {
        $this->_getResourceModel()->deleteOldAttempts();
        return $this;
    }

    /**
     * Delete Expired Captcha Images
     *
     * @return \Magento\Captcha\Model\Observer
     */
    public function deleteExpiredImages()
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
     */
    protected function _deleteExpiredImagesForWebsite(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Store\Model\Website $website = null,
        \Magento\Store\Model\Store $store = null
    ) {
        $expire = time() - $helper->getConfig('timeout', $store) * 60;
        $imageDirectory = $this->_mediaDirectory->getRelativePath($helper->getImgDir($website));
        foreach ($this->_mediaDirectory->read($imageDirectory) as $filePath) {
            if ($this->_mediaDirectory->isFile(
                $filePath
            ) && pathinfo(
                $filePath,
                PATHINFO_EXTENSION
            ) == 'png' && $this->_mediaDirectory->stat(
                $filePath
            )['mtime'] < $expire
            ) {
                $this->_mediaDirectory->delete($filePath);
            }
        }
    }

    /**
     * Get resource model
     *
     * @return \Magento\Captcha\Model\Resource\Log
     */
    protected function _getResourceModel()
    {
        return $this->_resLogFactory->create();
    }
}
