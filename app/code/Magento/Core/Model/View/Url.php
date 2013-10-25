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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Builds URLs for publicly accessible files
 */
namespace Magento\Core\Model\View;

class Url
{
    /**
     * XPath for configuration setting of signing static files
     */
    const XML_PATH_STATIC_FILE_SIGNATURE = 'dev/static/sign';

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dirs;

    /**
     * @var \Magento\Core\Model\View\Service
     */
    protected $_viewService;

    /**
     * @var \Magento\Core\Model\View\Publisher
     */
    protected $_publisher;

    /**
     * @var \Magento\Core\Model\View\DeployedFilesManager
     */
    protected $_deployedFileManager;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;


    /**
     * View files URL model
     *
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\App\Dir $dirs
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\View\Service $viewService
     * @param \Magento\Core\Model\View\Publisher $publisher
     * @param \Magento\Core\Model\View\DeployedFilesManager $deployedFileManager
     */
    public function __construct(
        \Magento\Filesystem $filesystem,
        \Magento\App\Dir $dirs,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\View\Service $viewService,
        \Magento\Core\Model\View\Publisher $publisher,
        \Magento\Core\Model\View\DeployedFilesManager $deployedFileManager
    ) {
        $this->_filesystem = $filesystem;
        $this->_dirs = $dirs;
        $this->_storeManager = $storeManager;
        $this->_viewService = $viewService;
        $this->_publisher = $publisher;
        $this->_deployedFileManager = $deployedFileManager;
    }

    /**
     * Get url to file base on theme file identifier.
     * Publishes file there, if needed.
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = array())
    {
        $isSecure = isset($params['_secure']) ? (bool) $params['_secure'] : null;
        unset($params['_secure']);

        $publicFilePath = $this->getViewFilePublicPath($fileId, $params);
        $url = $this->getPublicFileUrl($publicFilePath, $isSecure);

        return $url;
    }

    /**
     * Get public file path
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFilePublicPath($fileId, array $params = array())
    {
        $this->_viewService->updateDesignParams($params);
        $filePath = $this->_viewService->extractScope($fileId, $params);

        $publicFilePath = $this->_getFilesManager()->getPublicFilePath($filePath, $params);

        return $publicFilePath;
    }

    /**
     * Get url to public file
     *
     * @param string $publicFilePath
     * @param bool|null $isSecure
     * @return string
     * @throws \Magento\Exception
     */
    public function getPublicFileUrl($publicFilePath, $isSecure = null)
    {
        foreach (array(
                \Magento\Core\Model\Store::URL_TYPE_LIB     => \Magento\App\Dir::PUB_LIB,
                \Magento\Core\Model\Store::URL_TYPE_MEDIA   => \Magento\App\Dir::MEDIA,
                \Magento\Core\Model\Store::URL_TYPE_STATIC  => \Magento\App\Dir::STATIC_VIEW,
                \Magento\Core\Model\Store::URL_TYPE_CACHE   => \Magento\App\Dir::PUB_VIEW_CACHE,
            ) as $urlType => $dirType
        ) {
            $dir = $this->_dirs->getDir($dirType);
            if (strpos($publicFilePath, $dir) === 0) {
                $relativePath = ltrim(substr($publicFilePath, strlen($dir)), '\\/');
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
                $url = $this->_storeManager->getStore()->getBaseUrl($urlType, $isSecure) . $relativePath;

                if ($this->_isStaticFilesSigned() && $this->_viewService->isViewFileOperationAllowed()) {
                    $fileMTime = $this->_filesystem->getMTime($publicFilePath);
                    $url .= '?' . $fileMTime;
                }
                return $url;
            }
        }
        throw new \Magento\Exception(
            "Cannot build URL for the file '$publicFilePath' because it does not reside in a public directory."
        );
    }

    /**
     * Check if static files have to be signed
     *
     * @return bool
     */
    protected function _isStaticFilesSigned()
    {
        return (bool)$this->_storeManager->getStore()->getConfig(self::XML_PATH_STATIC_FILE_SIGNATURE);
    }

    /**
     * Get files manager that is able to return file public path
     *
     * @return \Magento\Core\Model\View\PublicFilesManagerInterface
     */
    protected function _getFilesManager()
    {
        if ($this->_viewService->isViewFileOperationAllowed()) {
            $filesManager = $this->_publisher;
        } else {
            $filesManager = $this->_deployedFileManager;
        }

        return $filesManager;
    }
}
