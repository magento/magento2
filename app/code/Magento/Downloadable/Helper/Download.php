<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Downloadable\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException as CoreException;

/**
 * Downloadable Products Download Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Download extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Link type url
     */
    const LINK_TYPE_URL = 'url';

    /**
     * Link type file
     */
    const LINK_TYPE_FILE = 'file';

    /**
     * Config path to content disposition
     */
    const XML_PATH_CONTENT_DISPOSITION = 'catalog/downloadable/content_disposition';

    /**
     * Type of link
     *
     * @var string
     * @since 2.0.0
     */
    protected $_linkType = self::LINK_TYPE_FILE;

    /**
     * Resource file
     *
     * @var string
     * @since 2.0.0
     */
    protected $_resourceFile = null;

    /**
     * Resource open handle
     *
     * @var \Magento\Framework\Filesystem\File\ReadInterface
     * @since 2.0.0
     */
    protected $_handle = null;

    /**
     * Remote server headers
     *
     * @var array
     * @since 2.0.0
     */
    protected $_urlHeaders = [];

    /**
     * MIME Content-type for a file
     *
     * @var string
     * @since 2.0.0
     */
    protected $_contentType = 'application/octet-stream';

    /**
     * File name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_fileName = 'download';

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     * @since 2.0.0
     */
    protected $_coreFileStorageDb;

    /**
     * Downloadable file
     *
     * @var \Magento\Downloadable\Helper\File
     * @since 2.0.0
     */
    protected $_downloadableFile;

    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     * @since 2.0.0
     */
    protected $fileReadFactory;

    /**
     * Working Directory (valid for LINK_TYPE_FILE only).
     * @var \Magento\Framework\Filesystem\Directory\Read
     * @since 2.0.0
     */
    protected $_workingDirectory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     * @since 2.0.0
     */
    protected $_session;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param File $downloadableFile
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param Filesystem\File\ReadFactory $fileReadFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory
    ) {
        parent::__construct($context);
        $this->_downloadableFile = $downloadableFile;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_filesystem = $filesystem;
        $this->_session = $session;
        $this->fileReadFactory = $fileReadFactory;
    }

    /**
     * Retrieve Resource file handle (socket, file pointer etc)
     *
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @throws CoreException|\Exception
     * @since 2.0.0
     */
    protected function _getHandle()
    {
        if (!$this->_resourceFile) {
            throw new CoreException(__('Please set resource file and link type.'));
        }

        if (is_null($this->_handle)) {
            if ($this->_linkType == self::LINK_TYPE_URL) {
                $path = $this->_resourceFile;
                $protocol = strtolower(parse_url($path, PHP_URL_SCHEME));
                if ($protocol) {
                    // Strip down protocol from path
                    $path = preg_replace('#.+://#', '', $path);
                }
                $this->_handle = $this->fileReadFactory->create($path, $protocol);
            } elseif ($this->_linkType == self::LINK_TYPE_FILE) {
                $this->_workingDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $fileExists = $this->_downloadableFile->ensureFileInFilesystem($this->_resourceFile);
                if ($fileExists) {
                    $this->_handle = $this->_workingDirectory->openFile($this->_resourceFile);
                } else {
                    throw new CoreException(__('Invalid download link type.'));
                }
            } else {
                throw new CoreException(__('Invalid download link type.'));
            }
        }
        return $this->_handle;
    }

    /**
     * Retrieve file size in bytes
     *
     * @return int
     * @since 2.0.0
     */
    public function getFileSize()
    {
        return $this->_getHandle()->stat($this->_resourceFile)['size'];
    }

    /**
     * Return MIME type of a file.
     *
     * @return string
     * @since 2.0.0
     */
    public function getContentType()
    {
        $this->_getHandle();
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            if (function_exists(
                'mime_content_type'
            ) && ($contentType = mime_content_type(
                $this->_workingDirectory->getAbsolutePath($this->_resourceFile)
            ))
            ) {
                return $contentType;
            } else {
                return $this->_downloadableFile->getFileType($this->_resourceFile);
            }
        } elseif ($this->_linkType == self::LINK_TYPE_URL) {
            return $this->_handle->stat($this->_resourceFile)['type'];
        }
        return $this->_contentType;
    }

    /**
     * Return name of the file
     *
     * @return string
     * @since 2.0.0
     */
    public function getFilename()
    {
        $this->_getHandle();
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            return pathinfo($this->_resourceFile, PATHINFO_BASENAME);
        } elseif ($this->_linkType == self::LINK_TYPE_URL) {
            $stat = $this->_handle->stat($this->_resourceFile);
            if (isset($stat['disposition'])) {
                $contentDisposition = explode('; ', $stat['disposition']);
                if (!empty($contentDisposition[1]) && preg_match(
                    '/filename=([^ ]+)/',
                    $contentDisposition[1],
                    $matches
                )
                ) {
                    return $matches[1];
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
     * @return $this
     * @throws \InvalidArgumentException
     * @since 2.0.0
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

        $this->_resourceFile = $resourceFile;
        $this->_linkType = $linkType;

        return $this;
    }

    /**
     * Output file contents
     *
     * @return void
     * @since 2.0.0
     */
    public function output()
    {
        $handle = $this->_getHandle();
        $this->_session->writeClose();
        while (true == ($buffer = $handle->read(1024))) {
            echo $buffer;
        }
    }

    /**
     * Use Content-Disposition: attachment
     *
     * @param mixed $store
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getContentDisposition($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CONTENT_DISPOSITION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
}
