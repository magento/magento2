<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Uri\Uri;
use Zend\Http\Client\Adapter\Socket;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\RequestInterface;

class PurgeCache
{
    /**
     * @var UriFactory
     */
    protected $uriFactory;

    /**
     * @var SocketFactory
     */
    protected $socketAdapterFactory;

    /**
     * @var InvalidateLogger
     */
    private $logger;

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
     * @param UriFactory $uriFactory
     * @param SocketFactory $socketAdapterFactory
     * @param InvalidateLogger $logger
     * @param Reader $configReader
     * @param RequestInterface $request
     */
    public function __construct(
        UriFactory $uriFactory,
        SocketFactory $socketAdapterFactory,
        InvalidateLogger $logger,
        DeploymentConfig $config,
        RequestInterface $request
    ) {
        $this->uriFactory = $uriFactory;
        $this->socketAdapterFactory = $socketAdapterFactory;
        $this->logger = $logger;
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Send curl purge request
     * to invalidate cache by tags pattern
     *
     * @param string $tagsPattern
     * @return void
     */
    public function sendPurgeRequest($tagsPattern)
    {
        $uri = $this->uriFactory->create();
        $socketAdapter = $this->socketAdapterFactory->create();
        $servers = $this->config->get(ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS)
            ?: [['host' => $this->request->getHttpHost()]];
        $headers = ['X-Magento-Tags-Pattern' => $tagsPattern];
        $socketAdapter->setOptions(['timeout' => 10]);
        foreach ($servers as $server) {
            $port = isset($server['port']) ? $server['port'] : self::DEFAULT_PORT;
            $uri->setScheme('http')
                ->setHost($server['host'])
                ->setPort($port);
            try {
                $socketAdapter->connect($server['host'], $port);
                $socketAdapter->write(
                    'PURGE',
                    $uri,
                    '1.1',
                    $headers
                );
                $socketAdapter->close();
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage(), compact('server', 'tagsPattern'));
            }
        }

        $this->logger->execute(compact('servers', 'tagsPattern'));
    }
}
