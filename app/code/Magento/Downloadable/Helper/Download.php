<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Helper;

use Exception;
use InvalidArgumentException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException as CoreException;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;

/**
 * Downloadable Products Download Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Download extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Link type for url
     */
    public const LINK_TYPE_URL = 'url';

    /**
     * Link type for file
     */
    public const LINK_TYPE_FILE = 'file';

    /**
     * Config path to content disposition
     */
    public const XML_PATH_CONTENT_DISPOSITION = 'catalog/downloadable/content_disposition';

    /**
     * Type of link
     *
     * @var string
     */
    protected $_linkType = self::LINK_TYPE_FILE;

    /**
     * @var string
     */
    protected $_resourceFile = null;

    /**
     * Resource open handle
     *
     * @var \Magento\Framework\Filesystem\File\ReadInterface
     */
    protected $_handle = null;

    /**
     * Remote server headers
     *
     * @var array
     */
    protected $_urlHeaders = [];

    /**
     * MIME Content-type for a file
     *
     * @var string
     */
    protected $_contentType = 'application/octet-stream';

    /**
     * @var string
     */
    protected $_fileName = 'download';

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_downloadableFile;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected $fileReadFactory;

    /**
     * Working Directory (valid for LINK_TYPE_FILE only).
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $_workingDirectory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param File $downloadableFile
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param Filesystem\File\ReadFactory $fileReadFactory
     * @param Mime|null $mime
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory,
        Mime $mime = null
    ) {
        parent::__construct($context);
        $this->_downloadableFile = $downloadableFile;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_filesystem = $filesystem;
        $this->_session = $session;
        $this->fileReadFactory = $fileReadFactory;
        $this->mime = $mime ?? ObjectManager::getInstance()->get(Mime::class);
    }

    /**
     * Retrieve Resource file handle (socket, file pointer etc)
     *
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @throws CoreException|Exception
     */
    protected function _getHandle()
    {
        if (!$this->_resourceFile) {
            throw new CoreException(__('Please set resource file and link type.'));
        }

        if ($this->_handle === null) {
            if ($this->_linkType == self::LINK_TYPE_URL) {
                $path = $this->_resourceFile;
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
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
     */
    public function getFileSize()
    {
        return $this->_getHandle()->stat($this->_resourceFile)['size'];
    }

    /**
     * Return MIME type of a file.
     *
     * @return string
     */
    public function getContentType()
    {
        $this->_getHandle();
        if ($this->_linkType === self::LINK_TYPE_FILE) {
            $absolutePath = $this->_workingDirectory->getAbsolutePath($this->_resourceFile);
            return $this->mime->getMimeType($absolutePath);
        }
        if ($this->_linkType === self::LINK_TYPE_URL) {
            return (is_array($this->_handle->stat($this->_resourceFile)['type'])
                ? end($this->_handle->stat($this->_resourceFile)['type'])
                : $this->_handle->stat($this->_resourceFile)['type']);
        }
        return $this->_contentType;
    }

    /**
     * Return name of the file
     *
     * @return string
     * phpcs:disable Magento2.Functions.DiscouragedFunction
     * phpcs:disable Generic.PHP.NoSilencedErrors
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
     * @throws InvalidArgumentException
     */
    public function setResource($resourceFile, $linkType = self::LINK_TYPE_FILE)
    {
        if (self::LINK_TYPE_FILE == $linkType) {
            //check LFI protection
            if ($resourceFile && preg_match('#\.\.[\\\/]#', $resourceFile)) {
                throw new InvalidArgumentException(
                    'Requested file may not include parent directory traversal ("../", "..\\" notation)'
                );
            }
        }

        $this->_resourceFile = $resourceFile;

        /**
        * check header for urls
        */
        if ($linkType === self::LINK_TYPE_URL) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $headers = array_change_key_case(get_headers($this->_resourceFile, 1), CASE_LOWER);
            if (isset($headers['location'])) {
                $this->_resourceFile  = is_array($headers['location']) ? current($headers['location'])
                    : $headers['location'];
            }
        }

        $this->_linkType = $linkType;
        return $this;
    }

    /**
     * Output file contents
     *
     * @return void
     */
    public function output()
    {
        $handle = $this->_getHandle();
        $this->_session->writeClose();
        while (true == ($buffer = $handle->read(1024))) {
            // phpcs:ignore Magento2.Security.LanguageConstruct
            echo $buffer; //@codingStandardsIgnoreLine
        }
    }

    /**
     * Use Content-Disposition: attachment
     *
     * @param mixed $store
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getContentDisposition($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTENT_DISPOSITION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Handle any exception thrown during command execution
     *
     * @param Exception $e
     * @param OutputInterface $output
     * @return int
     */
    public function handleException(Exception $e, OutputInterface $output): int
    {
        $output->writeln('<error>' . $e->getMessage() . '</error>');
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln($e->getTraceAsString());
        }
        return Cli::RETURN_FAILURE;
    }

    /**
     * Validate the input domains array
     *
     * @param array $domains
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateDomains(array $domains): void
    {
        if (empty($domains)) {
            throw new InvalidArgumentException('Error: Domains parameter is missing.');
        }
    }

    /**
     * Handle the \InvalidArgumentException exception.
     *
     * @param InvalidArgumentException $e
     * @param OutputInterface $output
     * @return int
     */
    public function handleInvalidArgumentException(InvalidArgumentException $e, OutputInterface $output): int
    {
        $output->writeln('<error>' . $e->getMessage() . '</error>');
        return Cli::RETURN_FAILURE;
    }
}
