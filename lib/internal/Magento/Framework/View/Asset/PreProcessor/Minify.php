<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @param AdapterInterface $adapter
     * @param Minification $minification
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
     */
    public function process(PreProcessor\Chain $chain)
    {
        if (
            $this->minification->isEnabled(pathinfo($chain->getTargetAssetPath(), PATHINFO_EXTENSION)) &&
            $this->minification->isMinifiedFilename($chain->getTargetAssetPath()) &&
            !$this->minification->isMinifiedFilename($chain->getOrigAssetPath())
        ) {
            $content = $this->adapter->minify($chain->getContent());
            $chain->setContent($content);
        }
    }
}
