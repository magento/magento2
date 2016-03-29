<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Built-in cache observer for layout elements rendering.
 */
class ProcessLayoutRenderElement implements ObserverInterface
{
    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     */
    protected $_config;

    /**
     * Is full page cache enabled flag
     *
     * @var bool
     */
    protected $isFullPageCacheEnabled;

    /**
     * Class constructor
     *
     * @param \Magento\PageCache\Model\Config $config
     */
    public function __construct(\Magento\PageCache\Model\Config $config)
    {
        $this->_config = $config;
    }

    /**
     * Is full page cache enabled
     *
     * @return bool
     */
    protected function isFullPageCacheEnabled()
    {
        if ($this->isFullPageCacheEnabled === null) {
            $this->isFullPageCacheEnabled = $this->_config->isEnabled();
        }
        return $this->isFullPageCacheEnabled;
    }

    /**
     * Add comment cache containers to private blocks.
     *
     * Blocks are wrapped only if page is cacheable.
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
            if ($block instanceof \Magento\Framework\View\Element\AbstractBlock && $block->isScopePrivate()) {
                $output = sprintf(
                    '<!-- BLOCK %1$s -->%2$s<!-- /BLOCK %1$s -->',
                    $block->getNameInLayout(),
                    $transport->getData('output')
                );
                $transport->setData('output', $output);
            }
        }
    }
}
