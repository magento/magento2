<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\App\FrontController;

use Magento\PageCache\Model\Config;
use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\ResultInterface;

/**
 * Varnish for processing builtin cache
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
     * @param Config $config
     * @param Version $version
     * @param AppState $state
     */
    public function __construct(Config $config, Version $version, AppState $state)
    {
        $this->config = $config;
        $this->version = $version;
        $this->state = $state;
    }

    /**
     * Perform response postprocessing
     *
     * @param FrontControllerInterface $subject
     * @param ResponseInterface|ResultInterface $result
     * @return ResponseHttp|ResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(FrontControllerInterface $subject, $result)
    {
        if ($this->config->getType() == Config::VARNISH && $this->config->isEnabled()
            && $result instanceof ResponseHttp
        ) {
            $this->version->process();

            if ($this->state->getMode() == AppState::MODE_DEVELOPER) {
                $result->setHeader('X-Magento-Debug', 1);
            }
        }

        return $result;
    }
}
