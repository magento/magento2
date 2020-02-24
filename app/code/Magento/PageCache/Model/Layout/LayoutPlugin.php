<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\Layout;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\Config;

/**
 * Appends cacheable pages response headers.
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
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @param ResponseInterface $response
     * @param Config $config
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(
        ResponseInterface $response,
        Config $config,
        MaintenanceMode $maintenanceMode
    ) {
        $this->response = $response;
        $this->config = $config;
        $this->maintenanceMode = $maintenanceMode;
    }

    /**
     * Sets appropriate Cache-Control headers.
     *
     * We have to set public headers in order to tell Varnish and Builtin app that page should be cached
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function afterGenerateElements(LayoutInterface $subject)
    {
        if ($subject->isCacheable() && !$this->maintenanceMode->isOn() && $this->config->isEnabled()) {
            $this->response->setPublicHeaders($this->config->getTtl());
        }
    }

    /**
     * Retrieves all identities from blocks for further cache invalidation.
     *
     * @param LayoutInterface $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGetOutput(LayoutInterface $subject, $result)
    {
        if ($subject->isCacheable() && $this->config->isEnabled()) {
            $tags = [[]];
            foreach ($subject->getAllBlocks() as $block) {
                if ($block instanceof IdentityInterface) {
                    $isEsiBlock = $block->getTtl() > 0;
                    $isVarnish = $this->config->getType() == Config::VARNISH;
                    if ($isVarnish && $isEsiBlock) {
                        continue;
                    }
                    $tags[] = $block->getIdentities();
                }
            }
            $tags = array_unique(array_merge(...$tags));
            $this->response->setHeader('X-Magento-Tags', implode(',', $tags));
        }

        return $result;
    }
}
