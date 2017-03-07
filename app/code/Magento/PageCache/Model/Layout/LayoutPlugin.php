<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
                if ($block instanceof \Magento\Framework\DataObject\IdentityInterface) {
                    $isEsiBlock = $block->getTtl() > 0;
                    $isVarnish = $this->config->getType() == \Magento\PageCache\Model\Config::VARNISH;
                    if ($isVarnish && $isEsiBlock) {
                        continue;
                    }
                    $tags = array_merge($tags, $block->getIdentities());
                }
            }
            $tags = array_unique($tags);
            $this->response->setHeader('X-Magento-Tags', implode(',', $tags));
        }
        return $result;
    }
}
