<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\Code\Minifier\AdapterInterface;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\PreProcessor;
use Magento\Framework\View\Asset\PreProcessorInterface;

/**
 * Assets minification pre-processor
 */
class Minify implements PreProcessorInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var Minification
     */
    protected $minification;

    /**
     * @var MinificationConfigProvider
     */
    private $minificationConfig;

    /**
     * @param AdapterInterface $adapter
     * @param Minification $minification
     * @param MinificationConfigProvider $minificationConfig
     */
    public function __construct(
        AdapterInterface $adapter,
        Minification $minification,
        MinificationConfigProvider $minificationConfig
    ) {
        $this->adapter = $adapter;
        $this->minification = $minification;
        $this->minificationConfig = $minificationConfig;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param PreProcessor\Chain $chain
     * @return void
     */
    public function process(PreProcessor\Chain $chain)
    {
        if ($this->minificationConfig->isMinificationEnabled($chain->getTargetAssetPath()) &&
            $this->minification->isMinifiedFilename($chain->getTargetAssetPath()) &&
            !$this->minification->isMinifiedFilename($chain->getOrigAssetPath())
        ) {
            $content = $this->adapter->minify($chain->getContent());
            $chain->setContent($content);
        }
    }
}
