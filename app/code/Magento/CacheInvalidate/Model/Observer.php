<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

use Symfony\Component\Config\Definition\Exception\Exception;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\RequestInterface;

/**
 * Class Observer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var UriFactory
     */
    protected $uriFactory;

    /**
     * @var SocketFactory
     */
    protected $socketAdapterFactory;

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
     * @param UriFactory $uriFactory
     * @param SocketFactory $socketAdapterFactory
     * @param InvalidateLogger $logger
     * @param DeploymentConfig $deploymentConfig
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        UriFactory $uriFactory,
        SocketFactory $socketAdapterFactory,
        InvalidateLogger $logger,
        DeploymentConfig $deploymentConfig,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->uriFactory = $uriFactory;
        $this->socketAdapterFactory = $socketAdapterFactory;
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
        /** @var \Zend\Uri\Uri $uri */
        $uri = $this->uriFactory->create();
        /** @var \Zend\Http\Client\Adapter\Socket $socketAdapter */
        $socketAdapter = $this->socketAdapterFactory->create();

        $servers = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS)
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
