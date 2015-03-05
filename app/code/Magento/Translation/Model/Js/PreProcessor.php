<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Js;

use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\Filesystem;

/**
 * PreProcessor responsible for replacing translation calls in js files to translated strings
 */
class PreProcessor implements PreProcessorInterface
{
    /**
     * Javascript translation configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param Chain $chain
     * @return void
     */
    public function process(Chain $chain)
    {
        if ($this->config->isEmbeddedStrategy()) {
            $chain->setContent($this->translate($chain->getContent()));
        }
    }

    /**
     * Replace translation calls with translation result and return content
     *
     * @param string $content
     * @return string
     */
    public function translate($content)
    {
        foreach ($this->config->getPatterns() as $pattern) {
            $content = preg_replace_callback($pattern, [$this, 'replaceCallback'], $content);
        }
        return $content;
    }

    /**
     * Replace callback for preg_replace_callback function
     *
     * @param array $matches
     * @return string
     */
    protected function replaceCallback($matches)
    {
        return '"' . __($matches[1]) . '"';
    }
}
