<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Cache;

use Zend\Uri\Uri;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\RequestInterface;
use Zend\Uri\UriFactory;

class Server
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * @var RequestInterface
     */
    private $request;

    const DEFAULT_PORT = 80;

    /**
     * Constructor
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param DeploymentConfig $config
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        DeploymentConfig $config,
        RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Get cache servers' Uris
     *
     * @return Uri[]
     */
    public function getUris()
    {
        $servers = [];
        $configuredHosts = $this->config->get(ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS);
        if (null == $configuredHosts) {
            $httpHost = $this->request->getHttpHost();
            $servers[] = $httpHost ?
                UriFactory::factory('')->setHost($httpHost)->setPort(self::DEFAULT_PORT)->setScheme('http') :
                UriFactory::factory($this->urlBuilder->getUrl('*', ['_nosid' => true])) // Don't use SID in building URL
                    ->setScheme('http')
                    ->setPath(null)
                    ->setQuery(null);

        } else {
            foreach ($configuredHosts as $host) {
                $servers[] = UriFactory::factory('')->setHost($host['host'])
                    ->setPort(isset($host['port']) ? $host['port'] : self::DEFAULT_PORT)
                    ->setScheme('http');
            }
        }
        return $servers;
    }
}
