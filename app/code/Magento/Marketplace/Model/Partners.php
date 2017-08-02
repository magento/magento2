<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Marketplace\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Marketplace\Helper\Cache;
use Magento\Backend\Model\UrlInterface;

/**
 * @api
 * @since 2.0.0
 */
class Partners
{
    /**
     * @var Curl
     * @since 2.0.0
     */
    protected $curlClient;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $urlPrefix = 'https://';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $apiUrl = 'magento.com/magento-connect/platinumpartners/list';

    /**
     * @var \Magento\Marketplace\Helper\Cache
     * @since 2.0.0
     */
    protected $cache;

    /**
     * @param Curl $curl
     * @param Cache $cache
     * @param UrlInterface $backendUrl
     * @since 2.0.0
     */
    public function __construct(Curl $curl, Cache $cache, UrlInterface $backendUrl)
    {
        $this->curlClient = $curl;
        $this->cache = $cache;
        $this->backendUrl = $backendUrl;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getApiUrl()
    {
        return $this->urlPrefix . $this->apiUrl;
    }

    /**
     * Gets partners json
     *
     * @return array
     * @since 2.0.0
     */
    public function getPartners()
    {
        $apiUrl = $this->getApiUrl();
        try {
            $this->getCurlClient()->post($apiUrl, []);
            $this->getCurlClient()->setOptions(
                [
                    CURLOPT_REFERER => $this->getReferer()
                ]
            );
            $response = json_decode($this->getCurlClient()->getBody(), true);
            if ($response['partners']) {
                $this->getCache()->savePartnersToCache($response['partners']);
                return $response['partners'];
            } else {
                return $this->getCache()->loadPartnersFromCache();
            }
        } catch (\Exception $e) {
            return $this->getCache()->loadPartnersFromCache();
        }
    }

    /**
     * @return Curl
     * @since 2.0.0
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }

    /**
     * @return cache
     * @since 2.0.0
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getReferer()
    {
        return \Magento\Framework\App\Request\Http::getUrlNoScript($this->backendUrl->getBaseUrl())
        . 'admin/marketplace/index/index';
    }
}
