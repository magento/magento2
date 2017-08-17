<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Cache;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\RequestInterface;
use Zend\Uri\Uri;
use Zend\Uri\UriFactory;

/**
 * Class \Magento\PageCache\Model\Cache\Server
 *
 */
class Server
{
    /**
     * @var UrlInterface
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
     * @param UrlInterface $urlBuilder
     * @param DeploymentConfig $config
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface $urlBuilder,
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

        if (is_array($configuredHosts)) {
            foreach ($configuredHosts as $host) {
                $servers[] = UriFactory::factory('')
                    ->setHost($host['host'])
                    ->setPort(isset($host['port']) ? $host['port'] : self::DEFAULT_PORT)
                ;
            }
        } elseif ($this->request->getHttpHost()) {
            $servers[] = UriFactory::factory('')->setHost($this->request->getHttpHost())->setPort(self::DEFAULT_PORT);
        } else {
            $servers[] = UriFactory::factory($this->urlBuilder->getUrl('*', ['_nosid' => true]));
        }

        foreach (array_keys($servers) as $key) {
            $servers[$key]->setScheme('http')
                ->setPath('/')
                ->setQuery(null);
        }
        return $servers;
    }
}
