<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Captcha
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Captcha\Model;

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
     * @var \Magento\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Core\Model\StoreManager
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
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Core\Model\StoreManager $storeManager
     */
    public function __construct(
        Resource\LogFactory $resLogFactory,
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Captcha\Helper\Adminhtml\Data $adminHelper,
        \Magento\App\Filesystem $filesystem,
        \Magento\Core\Model\StoreManager $storeManager
    ) {
        $this->_resLogFactory = $resLogFactory;
        $this->_helper = $helper;
        $this->_adminHelper = $adminHelper;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::MEDIA_DIR);
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
     * @param \Magento\Core\Model\Website|null $website
     * @param \Magento\Core\Model\Store|null $store
     * @return void
     */
    protected function _deleteExpiredImagesForWebsite(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Core\Model\Website $website = null,
        \Magento\Core\Model\Store $store = null
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

