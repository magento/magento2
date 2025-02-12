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
    private const DEFAULT_RAW_CONTENT_TYPE = 'application/octet-stream';

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
    private array $options = [
        'directoryCode' => DirectoryList::ROOT,
        'filePath' => null,
        // File name to send to the client
        'fileName' => null,
        'contentType' => null,
        'contentLength' => null,
        // Whether to remove the file after it is sent to the client
        'remove' => false,
        // Whether to send the file as attachment
        'attachment' => true
    ];

    /**
     * @param HttpRequest $request
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Context $context
     * @param DateTime $dateTime
     * @param ConfigInterface $sessionConfig
     * @param Http $response
     * @param Filesystem $filesystem
     * @param Mime $mime
     * @param array $options
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        HttpRequest $request,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Context $context,
        DateTime $dateTime,
        ConfigInterface $sessionConfig,
        Http $response,
        Filesystem $filesystem,
        Mime $mime,
        array $options = []
    ) {
        parent::__construct($request, $cookieManager, $cookieMetadataFactory, $context, $dateTime, $sessionConfig);
        $this->response = $response;
        $this->filesystem = $filesystem;
        $this->mime = $mime;
        $this->options = array_merge($this->options, $options);
        if (!isset($this->options['filePath'])) {
            if (!isset($this->options['fileName'])) {
                throw new InvalidArgumentException("File name is required.");
            }
            $this->options['contentType'] ??= self::DEFAULT_RAW_CONTENT_TYPE;
        }
    }

    /**
     * @inheritDoc
     */
    public function sendResponse()
    {
        $dir = $this->filesystem->getDirectoryRead($this->options['directoryCode']);
        $forceHeaders = true;
        if (isset($this->options['filePath'])) {
            if (!$dir->isExist($this->options['filePath'])) {
                throw new InvalidArgumentException("File '{$this->options['filePath']}' does not exists.");
            }
            $filePath = $this->options['filePath'];
            $this->options['contentType'] ??= $dir->stat($filePath)['mimeType']
                ?? $this->mime->getMimeType($dir->getAbsolutePath($filePath));
            $this->options['contentLength'] ??= $dir->stat($filePath)['size'];
            $this->options['fileName'] ??= basename($filePath);
        } else {
            $this->options['contentLength'] = mb_strlen((string) $this->response->getContent(), '8bit');
            $forceHeaders = false;
        }

        $this->response->setHttpResponseCode(200);
        if ($this->options['attachment']) {
            $this->response->setHeader(
                'Content-Disposition',
                'attachment; filename="' . $this->options['fileName'] . '"',
                $forceHeaders
            );
        }
        $this->response->setHeader('Content-Type', $this->options['contentType'], $forceHeaders)
            ->setHeader('Content-Length', $this->options['contentLength'], $forceHeaders)
            ->setHeader('Pragma', 'public', $forceHeaders)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', $forceHeaders)
            ->setHeader('Last-Modified', date('r'), $forceHeaders);

        if (isset($this->options['filePath'])) {
            $this->response->sendHeaders();
            if (!$this->request->isHead()) {
                $this->sendFileContent();
                $this->afterFileIsSent();
            }
        } else {
            $this->response->sendResponse();
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
        $this->response->clearHeader($name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBody($value)
    {
        $this->response->setBody($value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function appendBody($value)
    {
        $this->response->appendBody($value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->response->getContent();
    }

    /**
     * @inheritDoc
     */
    public function setContent($value)
    {
        $this->response->setContent($value);
        return $this;
    }

    /**
     * Sends file content to the client
     *
     * @return void
     * @throws FileSystemException
     */
    private function sendFileContent(): void
    {
        $dir = $this->filesystem->getDirectoryRead($this->options['directoryCode']);
        $stream = $dir->openFile($this->options['filePath'], 'r');
        while (!$stream->eof()) {
            // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
            echo $stream->read(1024);
        }
        $stream->close();
    }

    /**
     * Callback after file is sent to the client
     *
     * @return void
     * @throws FileSystemException
     */
    private function afterFileIsSent(): void
    {
        $this->response->clearBody();
        if ($this->options['remove']) {
            $dir = $this->filesystem->getDirectoryWrite($this->options['directoryCode']);
            $dir->delete($this->options['filePath']);
        }
    }
}
