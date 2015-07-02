<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\App\State;
use Magento\Framework\Code\Minifier\AdapterInterface;
use Magento\Framework\View\Asset\ConfigInterface;
use Magento\Framework\View\Asset\PreProcessor;
use Magento\Framework\View\Asset\PreProcessorInterface;

/**
 * Assets minification pre-processor
 */
class Minify implements PreProcessorInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @param ConfigInterface $config
     * @param AdapterInterface $adapter
     * @param State $appState
     */
    public function __construct(ConfigInterface $config, AdapterInterface $adapter, State $appState)
    {
        $this->config = $config;
        $this->adapter = $adapter;
        $this->appState = $appState;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param PreProcessor\Chain $chain
     * @return void
     */
    public function process(PreProcessor\Chain $chain)
    {
        $extension = pathinfo($chain->getTargetAssetPath(), PATHINFO_EXTENSION);
        if (
            $this->config->isAssetMinification($extension) &&
            substr($chain->getTargetAssetPath(), -5 - strlen($extension)) == '.min.' . $extension &&
            $this->appState->getMode() != State::MODE_DEVELOPER
        ) {
            $content = $this->adapter->minify($chain->getContent());
            $chain->setContent($content);
        }
    }
}
