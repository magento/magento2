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
    private array $options = [
        'directoryCode' => DirectoryList::ROOT,
        'filePath' => null,
        // File name to send to the client
        'fileName' => null,
        'contentType' => null,
        'contentLength' => null,
        // Whether to remove the file after it is sent to the client
        'remove' => false,
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
    }

    /**
     * @inheritDoc
     */
    public function sendResponse()
    {
        $dir = $this->filesystem->getDirectoryWrite($this->options['directoryCode']);
        if (!isset($this->options['filePath'])) {
            throw new InvalidArgumentException("File path is required.");
        }
        if (!$dir->isExist($this->options['filePath'])) {
            throw new InvalidArgumentException("File '{$this->options['filePath']}' does not exists.");
        }
        $filePath = $this->options['filePath'];
        $contentType = $this->options['contentType']
            ?? $dir->stat($filePath)['mimeType']
            ?? $this->mime->getMimeType($dir->getAbsolutePath($filePath));
        $contentLength = $this->options['contentLength']
            ?? $dir->stat($filePath)['size'];
        $fileName = $this->options['fileName']
            ?? basename($filePath);
        $this->response->setHttpResponseCode(200);
        $this->response->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', $contentLength, true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Last-Modified', date('r'), true);

        $this->response->sendHeaders();

        if (!$this->request->isHead()) {
            $stream = $dir->openFile($filePath, 'r');
            while (!$stream->eof()) {
                // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
                echo $stream->read(1024);
            }
            $stream->close();
            if ($this->options['remove']) {
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
        $this->response->clearHeader($name);
        return $this;
    }
}
