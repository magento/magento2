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
 * @since 100.0.2
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
     * @inheritdoc
     */
    public function getCurrentSecureUrl(\Magento\Framework\App\RequestInterface $request)
    {
        return $this->url->getBaseUrl('link', true) . ltrim($request->getPathInfo(), '/');
    }

    /**
     * @inheritdoc
     */
    public function shouldBeSecure($path)
    {
        $baseUrl = (string)$this->coreConfig->getValue(Store::XML_PATH_UNSECURE_BASE_URL, 'default');
        if (parse_url($baseUrl, PHP_URL_SCHEME) === 'https') {
            return true;
        }

        if ($this->backendConfig->isSetFlag(Store::XML_PATH_SECURE_IN_ADMINHTML)) {
            if ($this->backendConfig->isSetFlag('admin/url/use_custom')) {
                $adminBaseUrl = (string)$this->coreConfig->getValue('admin/url/custom', 'default');
            } else {
                $adminBaseUrl = (string)$this->coreConfig->getValue(Store::XML_PATH_SECURE_BASE_URL, 'default');
            }

            return parse_url($adminBaseUrl, PHP_URL_SCHEME) === 'https';
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultPath()
    {
        return $this->backendConfig->getValue('web/default/admin');
    }
}
