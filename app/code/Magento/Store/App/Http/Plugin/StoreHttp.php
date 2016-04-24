<?php

namespace Magento\Store\App\Http\Plugin;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;

class StoreHttp
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlInterface $url,
        RequestInterface $request
    ) {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->_request = $request;
    }

    /**
     * Update the request's pathInfo if we have a store on a url path
     *
     * This will make sure we match the correct area and so the requested url
     * will be routed via the correct path.
     *
     * @return void
     */
    public function beforeLaunch()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_WEB,
            $this->_storeManager->getStore()->isCurrentlySecure()
        );
        if ($baseUrl) {
            $uri = parse_url($baseUrl);
            if (isset($uri['path']) && '/' !== $uri['path']) {
                $newPath = str_replace(
                    $uri['path'],
                    '',
                    $this->_request->getPathInfo()
                );
                if ('/' !== substr($newPath, 0, 1)) {
                    $newPath = '/' . $newPath;
                }
                $this->_request->setPathInfo($newPath);
            }
        }
    }
}
