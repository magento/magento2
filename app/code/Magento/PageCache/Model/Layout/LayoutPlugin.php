<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\Layout;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Layout;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Model\Spi\PageCacheTagsPreprocessorInterface;

/**
 * Append cacheable pages response headers.
 */
class LayoutPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var PageCacheTagsPreprocessorInterface
     */
    private $pageCacheTagsPreprocessor;

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @param ResponseInterface $response
     * @param Config $config
     * @param MaintenanceMode $maintenanceMode
     * @param PageCacheTagsPreprocessorInterface|null $pageCacheTagsPreprocessor
     */
    public function __construct(
        ResponseInterface $response,
        Config $config,
        MaintenanceMode $maintenanceMode,
        ?PageCacheTagsPreprocessorInterface $pageCacheTagsPreprocessor = null
    ) {
        $this->response = $response;
        $this->config = $config;
        $this->maintenanceMode = $maintenanceMode;
        $this->pageCacheTagsPreprocessor = $pageCacheTagsPreprocessor
            ?? ObjectManager::getInstance()->get(PageCacheTagsPreprocessorInterface::class);
    }

    /**
     * Set appropriate Cache-Control headers.
     *
     * We have to set public headers in order to tell Varnish and Builtin app that page should be cached
     *
     * @param Layout $subject
     * @return void
     */
    public function afterGenerateElements(Layout $subject)
    {
        if ($subject->isCacheable() && !$this->maintenanceMode->isOn() && $this->config->isEnabled()) {
            $this->response->setPublicHeaders($this->config->getTtl());
        }
    }

    /**
     * Retrieve all identities from blocks for further cache invalidation.
     *
     * @param Layout $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGetOutput(Layout $subject, $result)
    {
        if ($subject->isCacheable() && $this->config->isEnabled()) {
            $tags = [];
            $isVarnish = $this->config->getType() === Config::VARNISH;

            foreach ($subject->getAllBlocks() as $block) {
                if ($block instanceof IdentityInterface) {
                    $isEsiBlock = $block->getTtl() > 0;
                    if ($isVarnish && $isEsiBlock) {
                        continue;
                    }
                    $tags[] = $block->getIdentities();
                }
            }
            $tags = array_unique(array_merge([], ...$tags));
            $tags = $this->pageCacheTagsPreprocessor->process($tags);
            $this->response->setHeader('X-Magento-Tags', implode(',', $tags));
        }

        return $result;
    }
}
