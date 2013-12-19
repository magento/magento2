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

namespace Magento\Downloadable\Helper;

use Magento\Filesystem;

/**
 * Downloadable Products Download Helper
 */
class Download extends \Magento\App\Helper\AbstractHelper
{
    /**
     * Link type url
     */
    const LINK_TYPE_URL         = 'url';

    /**
     * Link type file
     */
    const LINK_TYPE_FILE        = 'file';

    /**
     * Config path to content disposition
     */
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
    protected $_contentType = 'application/octet-stream';

    /**
     * File name
     *
     * @var string
     */
    protected $_fileName = 'download';

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb;

    /**
     * Downloadable file
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_downloadableFile;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

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
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * Working Directory (Could be MEDIA or HTTP).
     * @var \Magento\Filesystem\Directory\Read
     */
    protected $_workingDirectory;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Downloadable\Helper\File $downloadableFile
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Filesystem $filesystem
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Filesystem $filesystem
    ) {
        $this->_coreData = $coreData;
        $this->_downloadableFile = $downloadableFile;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_app = $context->getApp();
        $this->_filesystem = $filesystem;

        parent::__construct($context);
    }

    /**
     * Retrieve Resource file handle (socket, file pointer etc)
     *
     * @return \Magento\Filesystem\File\ReadInterface
     * @throws \Magento\Core\Exception|\Exception
     */
    protected function _getHandle()
    {
        if (!$this->_resourceFile) {
            throw new \Magento\Core\Exception(__('Please set resource file and link type.'));
        }

        if (is_null($this->_handle)) {
            if ($this->_linkType == self::LINK_TYPE_URL) {
                $this->_workingDirectory = $this->_filesystem->getDirectoryRead(Filesystem::HTTP);
                $this->_handle = $this->_workingDirectory->openFile($this->_resourceFile);
            } elseif ($this->_linkType == self::LINK_TYPE_FILE) {
                $this->_workingDirectory = $this->_filesystem->getDirectoryRead(Filesystem::MEDIA);
                $fileExists = $this->_downloadableFile->ensureFileInFilesystem($this->_resourceFile);
                if ($fileExists) {
                    $this->_handle = $this->_workingDirectory->openFile($this->_resourceFile, '???');
                } else {
                    throw new \Magento\Core\Exception(__('Invalid download link type.'));
                }
            } else {
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
        return $this->_workingDirectory->stat($this->_resourceFile)['size'];
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        $this->_getHandle();
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            if (function_exists('mime_content_type') && ($contentType = mime_content_type($this->_resourceFile))) {
                return $contentType;
            } else {
                return $this->_downloadableFile->getFileType($this->_resourceFile);
            }
        } elseif ($this->_linkType == self::LINK_TYPE_URL) {
            return $this->_workingDirectory->stat($this->_resourceFile)['type'];
        }
        return $this->_contentType;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        $this->_getHandle();
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            return pathinfo($this->_resourceFile, PATHINFO_BASENAME);
        } elseif ($this->_linkType == self::LINK_TYPE_URL) {
            $stat = $this->_workingDirectory->stat($this->_resourceFile);
            if (isset($stat['disposition'])) {
                $contentDisposition = explode('; ', $stat['disposition']);
                if (!empty($contentDisposition[1]) && strpos($contentDisposition[1], 'filename=') !== false) {
                    return substr($contentDisposition[1], 9);
                }
            }
            $fileName = @pathinfo($this->_resourceFile, PATHINFO_BASENAME);
            if ($fileName) {
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
            if (preg_match('#\.\.[\\\/]#', $resourceFile)) {
                throw new \InvalidArgumentException(
                    'Requested file may not include parent directory traversal ("../", "..\\" notation)'
                );
            }
        }

        $this->_resourceFile    = $resourceFile;
        $this->_linkType        = $linkType;

        return $this;
    }

    public function output()
    {
        $handle = $this->_getHandle();
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            while (true == ($buffer = $handle->read(1024))) {
                print $buffer;
            }
        } elseif ($this->_linkType == self::LINK_TYPE_URL) {
            while (!$handle->eof()) {
                print $handle->read(1024);
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
