<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Controller\Result;

use Magento\PageCache\Model\Config;
use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Registry;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\ResultInterface;

/**
 * Plugin for processing varnish cache
 */
class VarnishPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Version
     */
    private $version;

    /**
     * @var AppState
     */
    private $state;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Config $config
     * @param Version $version
     * @param AppState $state
     * @param Registry $registry
     */
    public function __construct(Config $config, Version $version, AppState $state, Registry $registry)
    {
        $this->config = $config;
        $this->version = $version;
        $this->state = $state;
        $this->registry = $registry;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @param ResponseHttp $response
     * @return ResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderResult(ResultInterface $subject, ResultInterface $result, ResponseHttp $response)
    {
        $usePlugin = $this->registry->registry('use_page_cache_plugin');

        if ($this->config->getType() == Config::VARNISH && $this->config->isEnabled() && $usePlugin) {
            $this->version->process();

            if ($this->state->getMode() == AppState::MODE_DEVELOPER) {
                $response->setHeader('X-Magento-Debug', 1);
            }
        }

        return $result;
    }
}
