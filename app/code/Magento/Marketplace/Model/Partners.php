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
 */
class Partners
{
    /**
     * @var Curl
     */
    protected $curlClient;

    /**
     * @var string
     */
    protected $urlPrefix = 'https://';

    /**
     * @var string
     */
    protected $apiUrl = 'magento.com/magento-connect/platinumpartners/list';

    /**
     * @var \Magento\Marketplace\Helper\Cache
     */
    protected $cache;

    /**
     * @param Curl $curl
     * @param Cache $cache
     * @param UrlInterface $backendUrl
     */
    public function __construct(Curl $curl, Cache $cache, UrlInterface $backendUrl)
    {
        $this->curlClient = $curl;
        $this->cache = $cache;
        $this->backendUrl = $backendUrl;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->urlPrefix . $this->apiUrl;
    }

    /**
     * Gets partners json
     *
     * @return array
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
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }

    /**
     * @return cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return \Magento\Framework\App\Request\Http::getUrlNoScript($this->backendUrl->getBaseUrl())
        . 'admin/marketplace/index/index';
    }
}
