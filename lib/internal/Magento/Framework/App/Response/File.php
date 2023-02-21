<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Response;

use InvalidArgumentException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\PageCache\NotCacheableInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File\Mime;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class File extends Http implements NotCacheableInterface
{
    /**
     * @var Http
     */
    private Http $response;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Mime
     */
    private Mime $mime;

    /**
     * @var array
     */
    private array $fileOptions = [
        'directoryCode' => DirectoryList::ROOT,
        'filePath' => null,
        // File name to send to the client
        'fileName' => null,
        'contentType' => null,
        'contentLength' => null,
        // Whether to remove after file is sent to the client
        'remove' => false,
    ];

    /**
     * @param HttpRequest $request
     * @param Http $response
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Context $context
     * @param DateTime $dateTime
     * @param ConfigInterface $sessionConfig
     * @param Filesystem $filesystem
     * @param Mime $mime
     * @param array $fileOptions
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @throws FileSystemException
     */
    public function __construct(
        HttpRequest $request,
        Http $response,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Context $context,
        DateTime $dateTime,
        ConfigInterface $sessionConfig,
        Filesystem $filesystem,
        Mime $mime,
        array $fileOptions = []
    ) {
        parent::__construct($request, $cookieManager, $cookieMetadataFactory, $context, $dateTime, $sessionConfig);
        $this->filesystem = $filesystem;
        $this->response = $response;
        $this->mime = $mime;
        $this->fileOptions = array_merge($this->fileOptions, $fileOptions);
        if (!isset($this->fileOptions['filePath'])) {
            throw new InvalidArgumentException("File path is required");
        }
        $dir = $this->filesystem->getDirectoryRead($this->fileOptions['directoryCode']);
        if (!$dir->isExist($this->fileOptions['filePath'])) {
            throw new InvalidArgumentException("File '{$this->fileOptions['filePath']}' does not exists.");
        }
        $this->setFileHeaders();
    }

    /**
     * @inheritDoc
     */
    public function sendResponse()
    {
        $this->response->sendHeaders();

        if (!$this->request->isHead()) {
            $dir = $this->filesystem->getDirectoryWrite($this->fileOptions['directoryCode']);
            $filePath = $this->fileOptions['filePath'];
            $stream = $dir->openFile($filePath, 'r');
            while (!$stream->eof()) {
                // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
                echo $stream->read(1024);
            }
            $stream->close();
            if ($this->fileOptions['remove']) {
                $dir->delete($filePath);
            }
            $this->response->clearBody();
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHeader($name, $value, $replace = false)
    {
        $this->response->setHeader($name, $value, $replace);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function clearHeader($name)
    {
        return $this->response->clearHeader($name);
    }

    /**
     * Set appropriate headers for the file attachment
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function setFileHeaders(): void
    {
        $dir = $this->filesystem->getDirectoryWrite($this->fileOptions['directoryCode']);
        $filePath = $this->fileOptions['filePath'];
        $contentType = $this->fileOptions['contentType']
            ?? $dir->stat($filePath)['mimeType']
            ?? $this->mime->getMimeType($dir->getAbsolutePath($filePath));
        $contentLength = $this->fileOptions['contentLength']
            ?? $dir->stat($filePath)['size'];
        $fileName = $this->fileOptions['fileName']
            ?? basename($filePath);
        $this->response->setHttpResponseCode(200);
        $this->response->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', $contentLength)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Last-Modified', date('r'), true);
    }
}
