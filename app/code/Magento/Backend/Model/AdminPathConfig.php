<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

use Magento\Framework\App\Router\PathConfigInterface;
use Magento\Store\Model\Store;

/**
 * Path config to be used in adminhtml area
 * @api
 */
class AdminPathConfig implements PathConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $coreConfig;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backendConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->coreConfig = $coreConfig;
        $this->backendConfig = $backendConfig;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    public function getCurrentSecureUrl(\Magento\Framework\App\RequestInterface $request)
    {
        return $this->url->getBaseUrl('link', true) . ltrim($request->getPathInfo(), '/');
    }

    /**
     * {@inheritdoc}
     *
     * @param string $path
     * @return bool
     */
    public function shouldBeSecure($path)
    {
        return parse_url(
            (string)$this->coreConfig->getValue(Store::XML_PATH_UNSECURE_BASE_URL, 'default'),
            PHP_URL_SCHEME
        ) === 'https'
        || $this->backendConfig->isSetFlag(Store::XML_PATH_SECURE_IN_ADMINHTML)
        && parse_url(
            (string)$this->coreConfig->getValue(Store::XML_PATH_SECURE_BASE_URL, 'default'),
            PHP_URL_SCHEME
        ) === 'https';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getDefaultPath()
    {
        return $this->backendConfig->getValue('web/default/admin');
    }
}
