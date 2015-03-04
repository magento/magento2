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
     * Pattern for applying translation
     * @var string
     */
    protected $pattern;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     * @param string $pattern
     */
    public function __construct(
        Config $config,
        $pattern = '~\$\.mage\.__\([\'|\"](.+?)[\'|\"]\)~'
    ) {
        $this->config = $config;
        $this->pattern = $pattern;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param Chain $chain
     * @return void
     */
    public function process(Chain $chain)
    {
        if ($this->config->isPublishingMode()) {
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
        return preg_replace_callback($this->pattern, [$this, 'replaceCallback'], $content);
    }

    /**
     * @param array $matches
     * @return string
     */
    public function replaceCallback($matches)
    {
        return '"' . __($matches[1]) . '"';
    }
}
