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
 * @since 2.0.0
 */
class Minify implements PreProcessorInterface
{
    /**
     * @var AdapterInterface
     * @since 2.0.0
     */
    protected $adapter;

    /**
     * @var Minification
     * @since 2.0.0
     */
    protected $minification;

    /**
     * @param AdapterInterface $adapter
     * @param Minification $minification
     * @since 2.0.0
     */
    public function __construct(AdapterInterface $adapter, Minification $minification)
    {
        $this->adapter = $adapter;
        $this->minification = $minification;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param PreProcessor\Chain $chain
     * @return void
     * @since 2.0.0
     */
    public function process(PreProcessor\Chain $chain)
    {
        if ($this->minification->isEnabled(pathinfo($chain->getTargetAssetPath(), PATHINFO_EXTENSION)) &&
            $this->minification->isMinifiedFilename($chain->getTargetAssetPath()) &&
            !$this->minification->isMinifiedFilename($chain->getOrigAssetPath())
        ) {
            $content = $this->adapter->minify($chain->getContent());
            $chain->setContent($content);
        }
    }
}
