<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Rss\Order\Grid;

/**
 * Class Link
 * @package Magento\Sales\Block\Adminhtml\Rss\Order\Grid
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'rss/order/grid/link.phtml';

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     * @since 2.0.0
     */
    protected $rssUrlBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        array $data = []
    ) {
        $this->rssUrlBuilder = $rssUrlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getLink()
    {
        return $this->rssUrlBuilder->getUrl($this->getLinkParams());
    }

    /**
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getLabel()
    {
        return __('New Order RSS');
    }

    /**
     * Check whether status notification is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    public function isRssAllowed()
    {
        return true;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getLinkParams()
    {
        return ['type' => 'new_order'];
    }
}
