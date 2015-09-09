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
/**
 * Class Observer
 */
class Observer
{
    /**
     * Default port for purge requests
     */
    const DEFAULT_PORT = 80;

    /**
     * Application config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var Socket
     */
    protected $socketAdapter;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param Uri $uri
     * @param Socket $socketAdapter
     * @param InvalidateLogger $logger
     * @param DeploymentConfig $deploymentConfig
     * @param RequestInterface $request
     * @internal param Reader $configReader
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        Uri $uri,
        Socket $socketAdapter,
        InvalidateLogger $logger,
        DeploymentConfig $deploymentConfig,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->uri = $uri;
        $this->socketAdapter = $socketAdapter;
        $this->logger = $logger;
        $this->deploymentConfig = $deploymentConfig;
        $this->request = $request;
    }

    /**
     * If Varnish caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function invalidateVarnish(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if ($object instanceof \Magento\Framework\Object\IdentityInterface) {
                $tags = [];
                $pattern = "((^|,)%s(,|$))";
                foreach ($object->getIdentities() as $tag) {
                    $tags[] = sprintf($pattern, preg_replace("~_\\d+$~", '', $tag));
                    $tags[] = sprintf($pattern, $tag);
                }
                $this->sendPurgeRequest(implode('|', array_unique($tags)));
            }
        }
    }

    /**
     * Flash Varnish cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function flushAllCache(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $this->sendPurgeRequest('.*');
        }
    }

    /**
     * Send curl purge request
     * to invalidate cache by tags pattern
     *
     * @param string $tagsPattern
     * @return void
     */
    protected function sendPurgeRequest($tagsPattern)
    {
        $servers = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS)
            ?: [['host' => $this->request->getHttpHost()]];
        $headers = ['X-Magento-Tags-Pattern' => $tagsPattern];
        $this->socketAdapter->setOptions(['timeout' => 10]);
        foreach ($servers as $server) {
            $port = isset($server['port']) ? $server['port'] : self::DEFAULT_PORT;
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
                $this->logger->critical($e->getMessage(), compact('server', 'tagsPattern'));
            }
        }

        $this->logger->execute(compact('servers', 'tagsPattern'));
    }
}
