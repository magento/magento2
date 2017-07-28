<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\Info\Buttons;

/**
 * Block of links in Order view page
 *
 * @api
 * @since 2.0.0
 */
class Rss extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'order/info/buttons/rss.phtml';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     * @since 2.0.0
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     * @since 2.0.0
     */
    protected $rssUrlBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
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
        return __('Subscribe to Order Status');
    }

    /**
     * Check whether status notification is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    public function isRssAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'rss/order/status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve order status url key
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     * @since 2.0.0
     */
    protected function getUrlKey($order)
    {
        $data = [
            'order_id' => $order->getId(),
            'increment_id' => $order->getIncrementId(),
            'customer_id' => $order->getCustomerId(),
        ];
        return base64_encode(json_encode($data));
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getLinkParams()
    {
        $order = $this->orderFactory->create()->load($this->_request->getParam('order_id'));
        return [
            'type' => 'order_status',
            '_secure' => true,
            '_query' => ['data' => $this->getUrlKey($order)]
        ];
    }
}
