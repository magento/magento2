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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View;

/**
 * Builds URLs for publicly accessible files
 */
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
     * @var \Magento\View\Service
     */
    protected $_viewService;

    /**
     * @var \Magento\View\Publisher
     */
    protected $_publisher;

    /**
     * @var \Magento\View\DeployedFilesManager
     */
    protected $_deployedFileManager;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\View\Url\ConfigInterface
     */
    protected $_config;

    /**
     * Map urls to app dirs
     *
     * @var array
     */
    protected $_fileUrlMap;

    /**
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\UrlInterface $urlBuilder
     * @param Url\ConfigInterface $config
     * @param Service $viewService
     * @param Publisher $publisher
     * @param DeployedFilesManager $deployedFileManager
     * @param array $fileUrlMap
     */
    public function __construct(
        \Magento\Filesystem $filesystem,
        \Magento\UrlInterface $urlBuilder,
        \Magento\View\Url\ConfigInterface $config,
        \Magento\View\Service $viewService,
        \Magento\View\Publisher $publisher,
        \Magento\View\DeployedFilesManager $deployedFileManager,
        array $fileUrlMap = array()
    ) {
        $this->_filesystem = $filesystem;
        $this->_urlBuilder = $urlBuilder;
        $this->_config = $config;
        $this->_viewService = $viewService;
        $this->_publisher = $publisher;
        $this->_deployedFileManager = $deployedFileManager;
        $this->_fileUrlMap = $fileUrlMap;
    }

    /**
     * Retrieve view file URL
     *
     * Get URL to file base on theme file identifier.
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
        foreach ($this->_fileUrlMap as $urlMap) {
            $dir = $this->_filesystem->getPath($urlMap['value']);
            $publicFilePath = str_replace('\\', '/', $publicFilePath);
            if (strpos($publicFilePath, $dir) === 0) {
                $relativePath = ltrim(substr($publicFilePath, strlen($dir)), '\\/');
                $url = $this->_urlBuilder->getBaseUrl(
                    array(
                        '_type' => $urlMap['key'],
                        '_secure' => $isSecure
                    )
                ) . $relativePath;

                if ($this->_isStaticFilesSigned() && $this->_viewService->isViewFileOperationAllowed()) {
                    $directory = $this->_filesystem->getDirectoryRead(\Magento\Filesystem::ROOT);
                    $fileMTime = $directory->stat($directory->getRelativePath($publicFilePath))['mtime'];
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
        return (bool)$this->_config->getValue(self::XML_PATH_STATIC_FILE_SIGNATURE);
    }

    /**
     * Get files manager that is able to return file public path
     *
     * @return \Magento\View\PublicFilesManagerInterface
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
