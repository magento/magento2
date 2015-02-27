<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Model;

use Magento\Framework\App\Rss\UrlBuilderInterface;

/**
 * Class UrlBuilder
 * @package Magento\Rss\Model
 */
class UrlBuilder implements UrlBuilderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
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
     */
    public function getUrl(array $queryParams = [])
    {
        if (!$this->config->getValue('rss/config/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return '';
        }

        return $this->urlBuilder->getUrl('rss/feed/index', $queryParams);
    }
}
