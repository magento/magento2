<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Layout;

/**
 * Class LayoutPlugin
 */
class LayoutPlugin
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * Is varnish enabled flag
     *
     * @var bool
     */
    protected $isVarnishEnabled;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\PageCache\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\PageCache\Model\Config $config
    ) {
        $this->response = $response;
        $this->config = $config;
    }

    /**
     * Set appropriate Cache-Control headers
     * We have to set public headers in order to tell Varnish and Builtin app that page should be cached
     *
     * @param \Magento\Framework\View\Layout $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGenerateXml(\Magento\Framework\View\Layout $subject, $result)
    {
        if ($subject->isCacheable() && $this->config->isEnabled()) {
            $this->response->setPublicHeaders($this->config->getTtl());
        }
        return $result;
    }

    /**
     * Retrieve all identities from blocks for further cache invalidation
     *
     * @param \Magento\Framework\View\Layout $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGetOutput(\Magento\Framework\View\Layout $subject, $result)
    {
        if ($subject->isCacheable() && $this->config->isEnabled()) {
            $tags = [];
            foreach ($subject->getAllBlocks() as $block) {
                if ($this->isCacheableByVarnish($block)) {
                    continue;
                }
                if ($block instanceof \Magento\Framework\DataObject\IdentityInterface) {
                    $tags = array_merge($tags, $block->getIdentities());
                }
            }
            $tags = array_unique($tags);
            $this->response->setHeader('X-Magento-Tags', implode(',', $tags));
        }
        return $result;
    }

    /**
     * Replace actual block output with ESI include if Varnish is enabled and the block has TTL specified.
     *
     * @param \Magento\Framework\View\Layout $subject
     * @param \Closure $proceed
     * @param string $name
     * @param bool $useCache
     * @return string
     */
    public function aroundRenderElement(
        \Magento\Framework\View\Layout $subject,
        \Closure $proceed,
        $name,
        $useCache = true
    ) {
        $layout = $subject;
        if ($this->config->isEnabled() && $layout->isCacheable()) {
            $block = $layout->getBlock($name);
            if ($this->isCacheableByVarnish($block)) {
                return $this->generateEsiInclude($block, $layout);
            }
        }
        return $proceed($name, $useCache);
    }

    /**
     * Check if provided block should be cached by varnish.
     *
     * @param \Magento\Framework\View\Element\BlockInterface $block
     * @return bool
     */
    private function isCacheableByVarnish($block)
    {
        return $this->isVarnishEnabled()
            && ($block instanceof \Magento\Framework\View\Element\AbstractBlock)
            && ($block->getTtl() > 0);
    }

    /**
     * Check if varnish cache engine is enabled.
     *
     * @return bool
     */
    private function isVarnishEnabled()
    {
        if ($this->isVarnishEnabled === null) {
            $this->isVarnishEnabled = ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH);
        }
        return $this->isVarnishEnabled;
    }

    /**
     * Generate ESI include directive for the specified block.
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @param \Magento\Framework\View\Layout $layout
     * @return string
     */
    private function generateEsiInclude(
        \Magento\Framework\View\Element\AbstractBlock $block,
        \Magento\Framework\View\Layout $layout
    ) {
        $url = $block->getUrl(
            'page_cache/block/esi',
            [
                'blocks' => json_encode([$block->getNameInLayout()]),
                'handles' => json_encode($layout->getUpdate()->getHandles())
            ]
        );
        return sprintf('<esi:include src="%s" />', $url);
    }
}
