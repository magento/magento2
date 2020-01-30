<?php
/**
 * PageCache controller
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Controller;

use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Layout\LayoutCacheKeyInterface;

/**
 * Page cache block controller abstract class
 */
abstract class Block extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Base64Json
     */
    private $base64jsonSerializer;

    /**
     * Layout cache keys to be able to generate different cache id for same handles
     *
     * @var LayoutCacheKeyInterface
     */
    private $layoutCacheKey;

    /**
     * @var string
     */
    private $layoutCacheKeyName = 'mage_pagecache';

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param Json $jsonSerializer
     * @param Base64Json $base64jsonSerializer
     * @param LayoutCacheKeyInterface $layoutCacheKey
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        Json $jsonSerializer = null,
        Base64Json $base64jsonSerializer = null,
        LayoutCacheKeyInterface $layoutCacheKey = null
    ) {
        parent::__construct($context);
        $this->translateInline = $translateInline;
        $this->jsonSerializer = $jsonSerializer
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);
        $this->base64jsonSerializer = $base64jsonSerializer
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Base64Json::class);
        $this->layoutCacheKey = $layoutCacheKey
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(LayoutCacheKeyInterface::class);
    }

    /**
     * Get blocks from layout by handles
     *
     * @return array [\Element\BlockInterface]
     */
    protected function _getBlocks()
    {
        $blocks = $this->getRequest()->getParam('blocks', '');
        $handles = $this->getHandles();

        if (!$handles || !$blocks) {
            return [];
        }
        $blocks = $this->jsonSerializer->unserialize($blocks);

        $layout = $this->_view->getLayout();
        $this->layoutCacheKey->addCacheKeys($this->layoutCacheKeyName);

        $this->_view->loadLayout($handles, true, true, false);
        $data = [];

        foreach ($blocks as $blockName) {
            $blockInstance = $layout->getBlock($blockName);
            if (is_object($blockInstance)) {
                $data[$blockName] = $blockInstance;
            }
        }

        return $data;
    }

    /**
     * Get handles
     *
     * @return array
     */
    private function getHandles(): array
    {
        $handles = $this->getRequest()->getParam('handles', '');
        $handles = !$handles ? [] : $this->base64jsonSerializer->unserialize($handles);
        $validHandles = [];
        foreach ($handles as $handle) {
            if (!preg_match('/[@\'\*\.\\\"]/i', $handle)) {
                $validHandles[] = $handle;
            }
        }
        return $validHandles;
    }
}
