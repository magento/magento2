<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Response extends Http implements
    \Magento\Framework\App\Response\FileInterface,
    \Magento\Framework\App\PageCache\NotCacheableInterface
{
    /**
     * @var \Magento\Framework\File\Transfer\Adapter\Http
     */
    protected $_transferAdapter;

    /**
     * Full path to file
     *
     * @var string
     */
    protected $_filePath;

    /**
     * Constructor
     *
     * @param HttpRequest $request
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\Http\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\File\Transfer\Adapter\Http $transferAdapter
     */
    public function __construct(
        HttpRequest $request,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Http\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\File\Transfer\Adapter\Http $transferAdapter
    ) {
        parent::__construct($request, $cookieManager, $cookieMetadataFactory, $context, $dateTime);
        $this->_transferAdapter = $transferAdapter;
    }

    /**
     * Send response
     *
     * @return void
     */
    public function sendResponse()
    {
        if ($this->_filePath && $this->getHttpResponseCode() == 200) {
            $options = [
                'filepath' => $this->_filePath,
                'headers' => $this->getHeaders(),
            ];
            $this->_transferAdapter->send($options);
        } else {
            parent::sendResponse();
        }
    }

    /**
     * @param string $path
     * @return void
     */
    public function setFilePath($path)
    {
        $this->_filePath = $path;
    }
}
