<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\EntitySpecificHandlesList;

class ProcessLayoutRenderElement implements ObserverInterface
{
    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     */
    private $_config;

    /**
     * Is varnish enabled flag
     *
     * @var bool
     */
    private $isVarnishEnabled;

    /**
     * Is full page cache enabled flag
     *
     * @var bool
     */
    private $isFullPageCacheEnabled;

    /**
     * @var EntitySpecificHandlesList
     */
    private $entitySpecificHandlesList;

    /**
     * @var Base64Json
     */
    private $base64jsonSerializer;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * Class constructor
     *
     * @param \Magento\PageCache\Model\Config $config
     * @param EntitySpecificHandlesList $entitySpecificHandlesList
     * @param Json $jsonSerializer
     * @param Base64Json $base64jsonSerializer
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        EntitySpecificHandlesList $entitySpecificHandlesList = null,
        Json $jsonSerializer = null,
        Base64Json $base64jsonSerializer = null
    ) {
        $this->_config = $config;
        $this->entitySpecificHandlesList = $entitySpecificHandlesList
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(EntitySpecificHandlesList::class);
        $this->jsonSerializer = $jsonSerializer
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);
        $this->base64jsonSerializer = $base64jsonSerializer
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Base64Json::class);
    }

    /**
     * Replace the output of the block, containing ttl attribute, with ESI tag
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @param \Magento\Framework\View\Layout $layout
     * @return string
     */
    private function _wrapEsi(
        \Magento\Framework\View\Element\AbstractBlock $block,
        \Magento\Framework\View\Layout $layout
    ) {
        $handles = $layout->getUpdate()->getHandles();
        $pageSpecificHandles = $this->entitySpecificHandlesList->getHandles();
        $url = $block->getUrl(
            'page_cache/block/esi',
            [
                'blocks' => $this->jsonSerializer->serialize([$block->getNameInLayout()]),
                'handles' => $this->base64jsonSerializer->serialize(
                    array_values(array_diff($handles, $pageSpecificHandles))
                )
            ]
        );
        // Varnish does not support ESI over HTTPS must change to HTTP
        $url = substr($url, 0, 5) === 'https' ? 'http' . substr($url, 5) : $url;
        return sprintf('<esi:include src="%s" />', $url);
    }

    /**
     * Is full page cache enabled
     *
     * @return bool
     */
    private function isFullPageCacheEnabled()
    {
        if ($this->isFullPageCacheEnabled === null) {
            $this->isFullPageCacheEnabled = $this->_config->isEnabled();
        }
        return $this->isFullPageCacheEnabled;
    }

    /**
     * Is varnish cache engine enabled
     *
     * @return bool
     */
    private function isVarnishEnabled()
    {
        if ($this->isVarnishEnabled === null) {
            $this->isVarnishEnabled = ($this->_config->getType() == \Magento\PageCache\Model\Config::VARNISH);
        }
        return $this->isVarnishEnabled;
    }

    /**
     * Add comment cache containers to private blocks
     * Blocks are wrapped only if page is cacheable
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $event->getLayout();
        if ($this->isFullPageCacheEnabled() && $layout->isCacheable()) {
            $name = $event->getElementName();
            /** @var \Magento\Framework\View\Element\AbstractBlock $block */
            $block = $layout->getBlock($name);
            $transport = $event->getTransport();
            if ($block instanceof \Magento\Framework\View\Element\AbstractBlock) {
                $blockTtl = $block->getTtl();
                $output = $transport->getData('output');
                if (isset($blockTtl) && $this->isVarnishEnabled()) {
                    $output = $this->_wrapEsi($block, $layout);
                } elseif ($block->isScopePrivate()) {
                    $output = sprintf(
                        '<!-- BLOCK %1$s -->%2$s<!-- /BLOCK %1$s -->',
                        $block->getNameInLayout(),
                        $output
                    );
                }
                $transport->setData('output', $output);
            }
        }
    }
}
