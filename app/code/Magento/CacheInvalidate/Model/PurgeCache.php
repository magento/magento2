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
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\RequestInterface;

class PurgeCache
{
    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var Socket
     */
    protected $socketAdapter;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * @var Reader
     */
    private $configReader;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Constructor
     *
     * @param Uri $uri
     * @param Socket $socketAdapter
     * @param InvalidateLogger $logger
     * @param Reader $configReader
     * @param RequestInterface $request
     */
    public function __construct(
        Uri $uri,
        Socket $socketAdapter,
        InvalidateLogger $logger,
        Reader $configReader,
        RequestInterface $request
    ) {
        $this->uri = $uri;
        $this->socketAdapter = $socketAdapter;
        $this->logger = $logger;
        $this->configReader = $configReader;
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
        $config = $this->configReader->load(\Magento\Framework\Config\File\ConfigFilePool::APP_ENV);
        $servers = isset($config[ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS])
            ? $config[ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS]
            : [['host' => $this->request->getHttpHost()]];
        $headers = ['X-Magento-Tags-Pattern' => $tagsPattern];
        $this->socketAdapter->setOptions(['timeout' => 10]);
        foreach ($servers as $server) {
            $port = isset($server['port']) ? $server['port'] : 80;
            $this->uri->setScheme('http')
                ->setHost($server['host'])
                ->setPort($port);
            try {
                $this->socketAdapter->connect($server['host'], $port);
                $this->socketAdapter->write(
                    'PURGE',
                    $this->uri,
                    '1.1',
                    $headers
                );
                $this->socketAdapter->close();
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage(), compact('hosts', 'tagsPattern'));
            }
        }

        $this->logger->execute(compact('hosts', 'tagsPattern'));
    }
}
