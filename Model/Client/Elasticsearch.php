<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Client;

use Magento\Framework\Exception\LocalizedException;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;

/**
 * Elasticsearch client
 */
class Elasticsearch
{
    /**
     * Elasticsearch Client instance
     *
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $clientOptions;

    /**
     * Initialize Elasticsearch Client
     *
     * @param array $options
     * @param \Elasticsearch\Client|null $elasticsearchClient
     * @throws LocalizedException
     */
    public function __construct(
        $options = [],
        $elasticsearchClient = null
    ) {
        if (empty($options['hostname']) || ((!empty($options['enable_auth']) &&
            ($options['enable_auth'] == 1)) && empty($options['username']))) {
            throw new LocalizedException(
                __('We were unable to perform the search because of a search engine misconfiguration.')
            );
        }
        $config = $this->buildConfig($options);
        if (!($elasticsearchClient instanceof \Elasticsearch\Client)) {
            $elasticsearchClient = \Elasticsearch\ClientBuilder::fromConfig($config);
        }
        $this->client = $elasticsearchClient;
        $this->clientOptions = $options;
    }

    /**
     * Ping the elasticseach client
     *
     * @return bool|array
     */
    public function ping()
    {
        try {
            if ($this->client->ping(['client' => ['timeout' => $this->clientOptions['timeout']]])) {
                $pingStatus = ['status' => 'OK'];
            }
        } catch (NoNodesAvailableException $e) {
            $pingStatus = false;
        }
        return $pingStatus;
    }

    /**
     * @param array $options
     * @return array
     */
    public function buildConfig($options = [])
    {
        $host = preg_replace('/[http|https]:\/\//i', '', $options['hostname']);
        $protocol = parse_url($options['hostname'], PHP_URL_SCHEME);
        if (!$protocol) {
            $protocol = 'http';
        }
        if (!empty($options['port'])) {
            $host .= ':' . $options['port'];
        }
        if (!empty($options['enable_auth']) && ($options['enable_auth'] == 1)) {
            $host = sprintf('%s://%s:%s@%s', $protocol, $options['username'], $options['password'], $host);
        }
        $config = [
            'hosts' => [
                $host
            ]
        ];
        return $config;
    }
}
