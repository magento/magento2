<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Captcha cron actions
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
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Captcha\Helper\Adminhtml\Data $adminHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManager $storeManager
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Captcha\Helper\Adminhtml\Data $adminHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManager $storeManager
    ) {
        $this->_helper = $helper;
        $this->_adminHelper = $adminHelper;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
    }

    /**
     * Delete Expired Captcha Images
     *
     * @return \Magento\Captcha\Cron\DeleteExpiredImages
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
     */
    protected function _deleteExpiredImagesForWebsite(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Store\Model\Website $website = null,
        \Magento\Store\Model\Store $store = null
    ) {
        $expire = time() - $helper->getConfig('timeout', $store) * 60;
        $imageDirectory = $this->_mediaDirectory->getRelativePath($helper->getImgDir($website));
        foreach ($this->_mediaDirectory->read($imageDirectory) as $filePath) {
            if ($this->_mediaDirectory->isFile($filePath)
                && pathinfo($filePath, PATHINFO_EXTENSION) == 'png'
                && $this->_mediaDirectory->stat($filePath)['mtime'] < $expire
            ) {
                $this->_mediaDirectory->delete($filePath);
            }
        }
    }
}
