<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Controller\Result;

use Magento\Framework\App\Response\Http as ResponseHttp;

/**
 * Plugin for processing builtin cache
 */
class BuiltinPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\PageCache\Kernel
     */
    protected $kernel;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\PageCache\Kernel $kernel
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\PageCache\Kernel $kernel,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Registry $registry
    ) {
        $this->config = $config;
        $this->kernel = $kernel;
        $this->state = $state;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Controller\ResultInterface $subject
     * @param callable $proceed
     * @param ResponseHttp $response
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRenderResult(
        \Magento\Framework\Controller\ResultInterface $subject,
        \Closure $proceed,
        ResponseHttp $response
    ) {
        $result = $proceed($response);
        $usePlugin = $this->registry->registry('use_page_cache_plugin');
        if (!$usePlugin || !$this->config->isEnabled()
            || $this->config->getType() != \Magento\PageCache\Model\Config::BUILT_IN
        ) {
            return $result;
        }

        if ($this->state->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
            $cacheControlHeader = $response->getHeader('Cache-Control');
            if ($cacheControlHeader instanceof \Zend\Http\Header\HeaderInterface) {
                $response->setHeader('X-Magento-Cache-Control', $cacheControlHeader->getFieldValue());
            }
            $response->setHeader('X-Magento-Cache-Debug', 'MISS', true);
        }

        $tagsHeader = $response->getHeader('X-Magento-Tags');
        $tags = [];
        if ($tagsHeader) {
            $tags = explode(',', $tagsHeader->getFieldValue());
            $response->clearHeader('X-Magento-Tags');
        }
        $tags = array_unique(array_merge($tags, [\Magento\PageCache\Model\Cache\Type::CACHE_TAG]));
        $response->setHeader('X-Magento-Tags', implode(',', $tags));

        $this->kernel->process($response);
        return $result;
    }
}
