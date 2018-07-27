<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Controller\Result;

use Magento\Framework\App\Response\Http as ResponseHttp;

/**
 * Plugin for processing varnish cache
 */
class VarnishPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\PageCache\Version
     */
    protected $version;

    /**
     * @var \Magento\Framework\App\PageCache\Kernel
     */
    protected $kernel;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\PageCache\Version $version
     * @param \Magento\Framework\App\PageCache\Kernel $kernel
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\PageCache\Version $version,
        \Magento\Framework\App\PageCache\Kernel $kernel,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Registry $registry
    ) {
        $this->config = $config;
        $this->version = $version;
        $this->kernel = $kernel;
        $this->state = $state;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Controller\ResultInterface $subject
     * @param callable $proceed
     * @param ResponseHttp $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function aroundRenderResult(
        \Magento\Framework\Controller\ResultInterface $subject,
        \Closure $proceed,
        ResponseHttp $response
    ) {
        $proceed($response);
        $usePlugin = $this->registry->registry('use_page_cache_plugin');
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()
            && $usePlugin) {
            $this->version->process();
            if ($this->state->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                $response->setHeader('X-Magento-Debug', 1);
            }
        }

        return $subject;
    }
}
