<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Category\Rss;

/**
 * Class Link
 * @api
 * @package Magento\Catalog\Block\Category\Rss
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry = null;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     * @since 2.0.0
     */
    protected $rssUrlBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->rssUrlBuilder = $rssUrlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function isRssAllowed()
    {
        return $this->_scopeConfig->getValue(
            'rss/catalog/category',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getLabel()
    {
        return __('Subscribe to RSS Feed');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getLinkParams()
    {
        return [
            'type' => 'category',
            'cid' => $this->registry->registry('current_category')->getId(),
            'store_id' => $this->_storeManager->getStore()->getId()
        ];
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isTopCategory()
    {
        return $this->registry->registry('current_category')->getLevel() == 2;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getLink()
    {
        return $this->rssUrlBuilder->getUrl($this->getLinkParams());
    }
}
