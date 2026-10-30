<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Downloadable\Helper;

use Laminas\Uri\Uri;
use Magento\Downloadable\Model\Url\DomainValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Exception\LocalizedException as CoreException;

/**
 * Downloadable Products Download Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.TooManyFields)
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

    private const CONNECT_TIMEOUT = 10;

    private const PROBE_TIMEOUT = 30;

    private const LOW_SPEED_LIMIT = 1;

    private const LOW_SPEED_TIME = 60;

    private const MAX_REDIRECTS = 3;

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

    /** @var CurlFactory */
    private $httpClientFactory;

    /** @var DomainValidator */
    private $domainValidator;

    /** @var bool */
    private $urlMetadataLoaded = false;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param File $downloadableFile
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param Filesystem\File\ReadFactory $fileReadFactory
     * @param Mime|null $mime
     * @param CurlFactory|null $httpClientFactory
     * @param DomainValidator|null $domainValidator
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory,
        ?Mime $mime = null,
        ?CurlFactory $httpClientFactory = null,
        ?DomainValidator $domainValidator = null
    ) {
        parent::__construct($context);
        $this->_downloadableFile = $downloadableFile;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_filesystem = $filesystem;
        $this->_session = $session;
        $this->fileReadFactory = $fileReadFactory;
        $this->mime = $mime ?? ObjectManager::getInstance()->get(Mime::class);
        $this->httpClientFactory = $httpClientFactory ?? ObjectManager::getInstance()->get(CurlFactory::class);
        $this->domainValidator = $domainValidator ?? ObjectManager::getInstance()->get(DomainValidator::class);
    }

    /**
     * Retrieve Resource file handle (socket, file pointer etc)
     *
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @throws CoreException|\Exception
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
        if ($this->_linkType === self::LINK_TYPE_URL) {
            $this->loadUrlMetadata();
            return (int) ($this->_urlHeaders['content-length'] ?? 0);
        }
        return $this->_getHandle()->stat($this->_resourceFile)['size'];
    }

    /**
     * Return MIME type of a file.
     *
     * @return string
     */
    public function getContentType()
    {
        if ($this->_linkType === self::LINK_TYPE_URL) {
            $this->loadUrlMetadata();
        } else {
            $this->_getHandle();
        }
        if ($this->_linkType === self::LINK_TYPE_FILE) {
            $absolutePath = $this->_workingDirectory->getAbsolutePath($this->_resourceFile);
            return $this->mime->getMimeType($absolutePath);
        }
        if ($this->_linkType === self::LINK_TYPE_URL) {
            return $this->_urlHeaders['content-type'] ?? $this->_contentType;
        }
        return $this->_contentType;
    }

    /**
     * Return name of the file
     *
     * @return string
     */
    public function getFilename()
    {
        if ($this->_linkType === self::LINK_TYPE_URL) {
            $this->loadUrlMetadata();
        } else {
            $this->_getHandle();
        }
        if ($this->_linkType == self::LINK_TYPE_FILE) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            return pathinfo($this->_resourceFile, PATHINFO_BASENAME);
        } elseif ($this->_linkType == self::LINK_TYPE_URL) {
            if (isset($this->_urlHeaders['content-disposition'])) {
                $contentDisposition = explode('; ', $this->_urlHeaders['content-disposition']);
                if (!empty($contentDisposition[1]) && preg_match(
                    '/filename=([^ ]+)/',
                    $contentDisposition[1],
                    $matches
                )
                ) {
                    return $matches[1];
                }
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $fileName = pathinfo((string) parse_url($this->_resourceFile, PHP_URL_PATH), PATHINFO_BASENAME);
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
     */
    public function setResource($resourceFile, $linkType = self::LINK_TYPE_FILE)
    {
        if (self::LINK_TYPE_FILE == $linkType) {
            //check LFI protection
            if ($resourceFile && preg_match('#\.\.[\\\/]#', $resourceFile)) {
                throw new \InvalidArgumentException(
                    'Requested file may not include parent directory traversal ("../", "..\\" notation)'
                );
            }
        }

        $this->_resourceFile = $resourceFile;

        if ($this->_linkType === self::LINK_TYPE_URL || $linkType === self::LINK_TYPE_URL) {
            if ($this->_handle !== null) {
                $this->_handle->close();
            }
            $this->_handle = null;
            $this->_urlHeaders = [];
            $this->urlMetadataLoaded = false;
        }

        $this->_linkType = $linkType;
        return $this;
    }

    /**
     * Cache headers from a GET stopped at the first body chunk, following validated redirects.
     *
     * @return void
     * @throws CoreException
     */
    private function loadUrlMetadata(): void
    {
        if ($this->urlMetadataLoaded) {
            return;
        }
        $url = (string) $this->_resourceFile;
        try {
            for ($redirects = 0; $redirects <= self::MAX_REDIRECTS; $redirects++) {
                $this->validateUrl($url);
                $response = $this->probeUrl($url);
                $headers = $response['headers'];
                $status = $response['status'];
                if ($status === 200) {
                    $this->_urlHeaders = $headers;
                    $this->_resourceFile = $url;
                    $this->urlMetadataLoaded = true;
                    return;
                }
                $url = $this->resolveRedirect($status, $headers, $url);
            }
            throw new \RuntimeException('Too many download redirects.');
        } catch (\Exception $exception) {
            $this->logUrlError($url, $exception);
            throw new CoreException(__('Something went wrong while getting the requested content.'), $exception);
        }
    }

    /**
     * Validate a URL before connecting to its host.
     *
     * @param string $url
     * @return void
     */
    private function validateUrl(string $url): void
    {
        $uri = new Uri($url);
        if (!in_array(strtolower((string) $uri->getScheme()), ['http', 'https'], true)
            || !$this->domainValidator->isValid($url)
        ) {
            throw new \RuntimeException('Invalid download URL.');
        }
    }

    /**
     * Configure a client and collect the most recent response header block.
     *
     * @param array $response
     * @return Curl
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function createHttpClient(array &$response): Curl
    {
        $response = ['status' => 0, 'headers' => []];
        $client = $this->httpClientFactory->create();
        $client->setTimeout(0);
        $client->setOptions([
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SUPPRESS_CONNECT_HEADERS => true,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT
        ]);
        $client->setOption(CURLOPT_HEADERFUNCTION, function ($curl, string $line) use (&$response): int {
            return $this->parseResponseHeader($line, $response);
        });
        return $client;
    }

    /**
     * Parse response headers independently of interim and proxy response blocks.
     *
     * @param string $line
     * @param array $response
     * @return int
     */
    private function parseResponseHeader(string $line, array &$response): int
    {
        if (str_starts_with($line, 'HTTP/')) {
            $parts = preg_split('/\s+/', trim($line), 3);
            $response = ['status' => (int) ($parts[1] ?? 0), 'headers' => []];
        } elseif (str_contains($line, ':')) {
            [$name, $value] = explode(':', $line, 2);
            $name = strtolower(trim($name));
            if ($name !== '') {
                $response['headers'][$name] = trim($value);
            }
        }
        return strlen($line);
    }

    /**
     * Read headers using GET, aborting intentionally when the response body starts.
     *
     * @param string $url
     * @return array
     */
    private function probeUrl(string $url): array
    {
        $response = [];
        $client = $this->createHttpClient($response);
        $client->setOption(CURLOPT_TIMEOUT, self::PROBE_TIMEOUT);
        $client->setOption(CURLOPT_LOW_SPEED_LIMIT, self::LOW_SPEED_LIMIT);
        $client->setOption(CURLOPT_LOW_SPEED_TIME, self::LOW_SPEED_TIME);
        $bodyStarted = false;
        $client->setOption(CURLOPT_WRITEFUNCTION, static function () use (&$bodyStarted): int {
            $bodyStarted = true;
            return 0;
        });
        try {
            $client->get($url);
        } catch (\Exception $exception) {
            if (!$bodyStarted) {
                throw $exception;
            }
        }
        return $response;
    }

    /**
     * Resolve a redirect without sending a request to the destination.
     *
     * @param int $status
     * @param array $headers
     * @param string $current
     * @return string
     */
    private function resolveRedirect(int $status, array $headers, string $current): string
    {
        $location = trim($headers['location'] ?? '');
        if (!in_array($status, [301, 302, 303, 307, 308], true) || $location === '') {
            throw new \RuntimeException('Invalid download response.');
        }
        return (new Uri($location))->resolve(new Uri($current))->toString();
    }

    /**
     * Stream the final URL after the session has been released.
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function outputUrl(): void
    {
        $this->loadUrlMetadata();
        $response = [];
        $transfer = [
            'disconnected' => false,
            'failure' => null,
            'checked' => false,
            'length' => null,
            'bytes' => 0
        ];
        try {
            $client = $this->createHttpClient($response);
            $writeChunk = function ($curl, string $chunk) use (&$response, &$transfer): int {
                return $this->writeUrlChunk($chunk, $response, $transfer);
            };
            $client->setOption(CURLOPT_WRITEFUNCTION, $writeChunk);
            try {
                $client->get($this->_resourceFile);
            } catch (\Exception $exception) {
                if (!$transfer['disconnected']) {
                    throw $transfer['failure'] ?? $exception;
                }
            }
            if ($transfer['disconnected']) {
                return;
            }
            $this->validateCompletedTransfer($response, $transfer);
        } catch (\Exception $exception) {
            $this->logUrlError($this->_resourceFile, $exception);
            throw new CoreException(__('Something went wrong while getting the requested content.'), $exception);
        }
    }

    /**
     * Write a successful chunk without exceeding the announced length.
     *
     * @param string $chunk
     * @param array $response
     * @param array $transfer
     * @return int
     */
    private function writeUrlChunk(string $chunk, array $response, array &$transfer): int
    {
        if (connection_aborted()) {
            $transfer['disconnected'] = true;
            return 0;
        }
        try {
            if ($response['status'] !== 200) {
                throw new \RuntimeException('Invalid download response.');
            }
            $this->validateStreamLength($response['headers'], $transfer);
            if ($transfer['length'] !== null && strlen($chunk) > $transfer['length'] - $transfer['bytes']) {
                throw new \RuntimeException('Download size changed.');
            }
        } catch (\Exception $exception) {
            $transfer['failure'] = $exception;
            return 0;
        }
        // phpcs:ignore Magento2.Security.LanguageConstruct
        echo $chunk;
        $transfer['bytes'] += strlen($chunk);
        return strlen($chunk);
    }

    /**
     * Compare metadata and streaming lengths before the first body chunk.
     *
     * @param array $headers
     * @param array $transfer
     * @return void
     */
    private function validateStreamLength(array $headers, array &$transfer): void
    {
        if ($transfer['checked']) {
            return;
        }
        $metadataLength = isset($this->_urlHeaders['content-length'])
            ? (int) $this->_urlHeaders['content-length']
            : null;
        $streamLength = isset($headers['content-length']) ? (int) $headers['content-length'] : null;
        if ($metadataLength !== null && $streamLength !== null && $metadataLength !== $streamLength) {
            throw new \RuntimeException('Download size changed.');
        }
        $transfer['length'] = $metadataLength ?? $streamLength;
        $transfer['checked'] = true;
    }

    /**
     * Reject unsuccessful responses and incomplete bodies, including empty transfers.
     *
     * @param array $response
     * @param array $transfer
     * @return void
     */
    private function validateCompletedTransfer(array $response, array &$transfer): void
    {
        if ($response['status'] !== 200) {
            throw new \RuntimeException('Invalid download response.');
        }
        $this->validateStreamLength($response['headers'], $transfer);
        if ($transfer['length'] !== null && $transfer['bytes'] !== $transfer['length']) {
            throw new \RuntimeException('Download size changed.');
        }
    }

    /**
     * Log the failure with its host, excluding the resource path and query.
     *
     * @param string $url
     * @param \Exception $exception
     * @return void
     */
    private function logUrlError(string $url, \Exception $exception): void
    {
        try {
            $host = (new Uri($url))->getHost();
        } catch (\Exception $invalidUrl) {
            $host = '';
        }
        $this->_logger->warning('Download failed for host "{host}": {message}', [
            'host' => $host,
            'message' => $exception->getMessage()
        ]);
    }

    /**
     * Output file contents
     *
     * @return void
     */
    public function output()
    {
        if ($this->_linkType === self::LINK_TYPE_URL) {
            $this->_session->writeClose();
            $this->outputUrl();
            return;
        }
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
}
