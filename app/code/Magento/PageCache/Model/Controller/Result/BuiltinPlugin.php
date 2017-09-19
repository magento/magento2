<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Controller\Result;

use Magento\PageCache\Model\Config;
use Magento\Framework\App\PageCache\Kernel;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Zend\Http\Header\HeaderInterface as HttpHeaderInterface;
use Magento\PageCache\Model\Cache\Type as CacheType;

/**
 * Plugin for processing builtin cache
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuiltinPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var AppState
     */
    private $state;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Config $config
     * @param Kernel $kernel
     * @param AppState $state
     * @param Registry $registry
     */
    public function __construct(Config $config, Kernel $kernel, AppState $state, Registry $registry)
    {
        $this->config = $config;
        $this->kernel = $kernel;
        $this->state = $state;
        $this->registry = $registry;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @param ResponseHttp $response
     * @return ResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderResult(ResultInterface $subject, ResultInterface $result, ResponseHttp $response)
    {
        $usePlugin = $this->registry->registry('use_page_cache_plugin');

        if (!$usePlugin || !$this->config->isEnabled() || $this->config->getType() != Config::BUILT_IN) {
            return $result;
        }

        if ($this->state->getMode() == AppState::MODE_DEVELOPER) {
            $cacheControlHeader = $response->getHeader('Cache-Control');

            if ($cacheControlHeader instanceof HttpHeaderInterface) {
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

        $tags = array_unique(array_merge($tags, [CacheType::CACHE_TAG]));
        $response->setHeader('X-Magento-Tags', implode(',', $tags));
        $this->kernel->process($response);

        return $result;
    }
}
