<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Controller;

use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Validator\RegexFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Layout\LayoutCacheKeyInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

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
     * @var RegexFactory
     */
    private RegexFactory $regexValidatorFactory;

    /**
     * Validation pattern for handles array
     */
    private const VALIDATION_RULE_PATTERN = '/^[a-z0-9]+[a-z0-9_]*$/i';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Handle size system name
     */
    private const XML_HANDLES_SIZE = 'system/full_page_cache/handles_size';

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param Json $jsonSerializer
     * @param Base64Json $base64jsonSerializer
     * @param LayoutCacheKeyInterface $layoutCacheKey
     * @param RegexFactory|null $regexValidatorFactory
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        ?Json $jsonSerializer = null,
        ?Base64Json $base64jsonSerializer = null,
        ?LayoutCacheKeyInterface $layoutCacheKey = null,
        ?RegexFactory $regexValidatorFactory = null,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        parent::__construct($context);
        $this->translateInline = $translateInline;
        $this->jsonSerializer = $jsonSerializer
            ?: ObjectManager::getInstance()->get(Json::class);
        $this->base64jsonSerializer = $base64jsonSerializer
            ?: ObjectManager::getInstance()->get(Base64Json::class);
        $this->layoutCacheKey = $layoutCacheKey
            ?: ObjectManager::getInstance()->get(LayoutCacheKeyInterface::class);
        $this->regexValidatorFactory = $regexValidatorFactory
            ?: ObjectManager::getInstance()->get(RegexFactory::class);
        $this->config = $scopeConfig
            ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Get blocks from layout by handles
     *
     * @return array [\Element\BlockInterface]
     */
    protected function _getBlocks()
    {
        $blocks = $this->getRequest()->getParam('blocks', '');
        $handles = $this->getRequest()->getParam('handles', '');

        if (!$handles || !$blocks) {
            return [];
        }
        $blocks = $this->jsonSerializer->unserialize($blocks);
        $handles = $this->base64jsonSerializer->unserialize($handles);

        $handleSize = (int) $this->config->getValue(self::XML_HANDLES_SIZE);
        $handles = ($handleSize && count($handles) > $handleSize)
            ? array_splice($handles, 0, $handleSize) : $handles;

        if (!$this->validateHandleParam($handles)) {
            return [];
        }

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
     * Validates handles parameter
     *
     * @param array $handles
     * @return bool
     */
    private function validateHandleParam($handles): bool
    {
        $validator = $this->regexValidatorFactory->create(['pattern' => self::VALIDATION_RULE_PATTERN]);
        foreach ($handles as $handle) {
            if ($handle && !$validator->isValid($handle)) {
                return false;
            }
        }

        return true;
    }
}
