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
 * @package     Magento_Downloadable
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Downloadable Products Download Helper
 *
 * @category    Magento
 * @package     Magento_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloadable\Helper;

class Download extends \Magento\Core\Helper\AbstractHelper
{
    const LINK_TYPE_URL         = 'url';
    const LINK_TYPE_FILE        = 'file';

    const XML_PATH_CONTENT_DISPOSITION  = 'catalog/downloadable/content_disposition';

    /**
     * Type of link
     *
     * @var string
     */
    protected $_linkType        = self::LINK_TYPE_FILE;

    /**
     * Resource file
     *
     * @var string
     */
    protected $_resourceFile    = null;

    /**
     * Resource open handle
     *
     * @var resource
     */
    protected $_handle          = null;

    /**
     * Remote server headers
     *
     * @var array
     */
    protected $_urlHeaders      = array();

    /**
     * MIME Content-type for a file
     *
     * @var string
     */
    protected $_contentType     = 'application/octet-stream';

    /**
     * File name
     *
     * @var string
     */
    protected $_fileName        = 'download';

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb = null;

    /**
     * Downloadable file
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_downloadableFile = null;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dirModel;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Downloadable\Helper\File $downloadableFile
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\App $app
     * @param \Magento\App\Dir $dirModel
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\App $app,
        \Magento\App\Dir $dirModel
    ) {
        $this->_coreData = $coreData;
        $this->_downloadableFile = $downloadableFile;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_app = $app;
        $this->_dirModel = $dirModel;
        parent::__construct($context);
    }

    /**
     * Retrieve Resource file handle (socket, file pointer etc)
     *
     * @return resource
     */
    protected function _getHandle()
    {
        if (!$this->_resourceFile) {
            throw new \Magento\Core\Exception(__('Please set resource file and link type.'));
        }

        if (is_null($this->_handle)) {
            if ($this->_linkType == self::LINK_TYPE_URL) {
                $port = 80;

                /**
                 * Validate URL
                 */
                $urlProp = parse_url($this->_resourceFile);
                if (!isset($urlProp['scheme']) || strtolower($urlProp['scheme'] != 'http')) {
                    throw new \Magento\Core\Exception(__('Please correct the download URL scheme.'));
                }
                if (!isset($urlProp['host'])) {
                    throw new \Magento\Core\Exception(__('Please correct the download URL host.'));
                }
                $hostname = $urlProp['host'];

                if (isset($urlProp['port'])) {
                    $port = (int)$urlProp['port'];
                }

                $path = '/';
                if (isset($urlProp['path'])) {
                    $path = $urlProp['path'];
                }
                $query = '';
                if (isset($urlProp['query'])) {
                    $query = '?' . $urlProp['query'];
                }

                try {
                    $this->_handle = fsockopen($hostname, $port, $errno, $errstr);
                }
                catch (\Exception $e) {
                    throw $e;
                }

                if ($this->_handle === false) {
                    throw new \Magento\Core\Exception(
                        __('Something went wrong connecting to the host. Error: %1.', $errstr)
                    );

                }

                $headers = 'GET ' . $path . $query . ' HTTP/1.0' . "\r\n"
                    . 'Host: ' . $hostname . "\r\n"
                    . 'User-Agent: Magento ver/' . \Magento\Core\Model\App::VERSION . "\r\n"
                    . 'Connection: close' . "\r\n"
                    . "\r\n";
                fwrite($this->_handle, $headers);

                while (!feof($this->_handle)) {
                    $str = fgets($this->_handle, 1024);
                    if ($str == "\r\n") {
                        break;
                    }
                    $match = array();
                    if (preg_match('#^([^:]+): (.*)\s+$#', $str, $match)) {
                        $k = strtolower($match[1]);
                        if ($k == 'set-cookie') {
                            continue;
                        }
                        else {
                            $this->_urlHeaders[$k] = trim($match[2]);
                        }
                    }
                    elseif (preg_match('#^HTTP/[0-9\.]+ (\d+) (.*)\s$#', $str, $match)) {
                        $this->_urlHeaders['code'] = $match[1];
                        $this->_urlHeaders['code-string'] = trim($match[2]);
                    }
                }

                if (!isset($this->_urlHeaders['code']) || $this->_urlHeaders['code'] != 200) {
                    throw new \Magento\Core\Exception(__('Something went wrong while getting the requested content.'));

                }
            }
            elseif ($this->_linkType == self::LINK_TYPE_FILE) {
                $this->_handle = new \Magento\Io\File();
                if (!is_file($this->_resourceFile)) {
                    $this->_coreFileStorageDb->saveFileToFilesystem($this->_resourceFile);
                }
                $this->_handle->open(array('path' => $this->_dirModel->getDir(\Magento\App\Dir::VAR_DIR)));
                if (!$this->_handle->fileExists($this->_resourceFile, true)) {
                    throw new \Magento\Core\Exception(__("We can't find this file."));
                }
                $this->_handle->streamOpen($this->_resourceFile, 'r');
            }
            else {
                throw new \Magento\Core\Exception(__('Invalid download link type.'));
            }
        }
        return $this->_handle;
    }

    /**
     * Retrieve file size in bytes
     */
    public function getFilesize()
    {
        $handle = $this->_getHandle();
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            return $handle->streamStat('size');
        }
        elseif ($this->_linkType == self::LINK_TYPE_URL) {
            if (isset($this->_urlHeaders['content-length'])) {
                return $this->_urlHeaders['content-length'];
            }
        }
        return null;
    }

    public function getContentType()
    {
        $handle = $this->_getHandle();
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            if (function_exists('mime_content_type') && ($contentType = mime_content_type($this->_resourceFile))) {
                return $contentType;
            } else {
                return $this->_downloadableFile->getFileType($this->_resourceFile);
            }
        }
        elseif ($this->_linkType == self::LINK_TYPE_URL) {
            if (isset($this->_urlHeaders['content-type'])) {
                $contentType = explode('; ', $this->_urlHeaders['content-type']);
                return $contentType[0];
            }
        }
        return $this->_contentType;
    }

    public function getFilename()
    {
        $handle = $this->_getHandle();
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            return pathinfo($this->_resourceFile, PATHINFO_BASENAME);
        }
        elseif ($this->_linkType == self::LINK_TYPE_URL) {
            if (isset($this->_urlHeaders['content-disposition'])) {
                $contentDisposition = explode('; ', $this->_urlHeaders['content-disposition']);
                if (!empty($contentDisposition[1]) && strpos($contentDisposition[1], 'filename=') !== false) {
                    return substr($contentDisposition[1], 9);
                }
            }
            if ($fileName = @pathinfo($this->_resourceFile, PATHINFO_BASENAME)) {
                return $fileName;
            }
        }
        return $this->_fileName;
    }

    /**
     * Set resource file for download
     *
     * @param string $resourceFile
     * @param string $linkType
     * @return \Magento\Downloadable\Helper\Download
     */
    public function setResource($resourceFile, $linkType = self::LINK_TYPE_FILE)
    {
        if (self::LINK_TYPE_FILE == $linkType) {
            //check LFI protection
            /** @var $helper \Magento\Core\Helper\Data */
            $helper = $this->_coreData;
            $helper->checkLfiProtection($resourceFile);
        }

        $this->_resourceFile    = $resourceFile;
        $this->_linkType        = $linkType;

        return $this;
    }

    /**
     * Retrieve Http Request Object
     *
     * @return \Magento\App\RequestInterface
     */
    public function getHttpRequest()
    {
        return $this->_app->getFrontController()->getRequest();
    }

    /**
     * Retrieve Http Response Object
     *
     * @return \Magento\App\ResponseInterface
     */
    public function getHttpResponse()
    {
        return $this->_app->getFrontController()->getResponse();
    }

    public function output()
    {
        $handle = $this->_getHandle();
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            while ($buffer = $handle->streamRead()) {
                print $buffer;
            }
        }
        elseif ($this->_linkType == self::LINK_TYPE_URL) {
            while (!feof($handle)) {
                print fgets($handle, 1024);
            }
        }
    }

    /**
     * Use Content-Disposition: attachment
     *
     * @param mixed $store
     * @return bool
     */
    public function getContentDisposition($store = null)
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_CONTENT_DISPOSITION, $store);
    }
}
