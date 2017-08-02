<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Model;

use Magento\Framework\App\Rss\UrlBuilderInterface;

/**
 * Class UrlBuilder
 * @package Magento\Rss\Model
 * @since 2.0.0
 */
class UrlBuilder implements UrlBuilderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $config;

    /**
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->config = $scopeConfig;
    }

    /**
     * @param array $queryParams
     * @return string
     * @since 2.0.0
     */
    public function getUrl(array $queryParams = [])
    {
        if (!$this->config->getValue('rss/config/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return '';
        }

        return $this->urlBuilder->getUrl('rss/feed/index', $queryParams);
    }
}
