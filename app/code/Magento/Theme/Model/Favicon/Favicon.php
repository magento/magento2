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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Theme\Model\Favicon;

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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $fileStorageDatabase;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase
     * @param \Magento\Framework\App\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\Framework\App\Filesystem $filesystem
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->fileStorageDatabase = $fileStorageDatabase;
        $this->mediaDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::MEDIA_DIR);
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
        $folderName = \Magento\Backend\Model\Config\Backend\Image\Favicon::UPLOAD_DIR;
        $scopeConfig = $this->scopeConfig->getValue(
            'design/head/shortcut_icon',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $scopeConfig;
        $faviconUrl = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;

        if (!is_null($scopeConfig) && $this->checkIsFile($path)) {
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
