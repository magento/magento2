<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GoogleAnalytics\Block;

/**
 * GoogleAnalytics Page Block
 *
 * @api
 */
class Ga extends \Magento\Framework\View\Element\Template
{
    /**
     * Google analytics data
     *
     * @var \Magento\GoogleAnalytics\Helper\Data
     */
    protected $_googleAnalyticsData = null;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_salesOrderCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection
     * @param \Magento\GoogleAnalytics\Helper\Data $googleAnalyticsData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection,
        \Magento\GoogleAnalytics\Helper\Data $googleAnalyticsData,
        array $data = []
    ) {
        $this->_googleAnalyticsData = $googleAnalyticsData;
        $this->_salesOrderCollection = $salesOrderCollection;
        parent::__construct($context, $data);
    }

    /**
     * Get config
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName()
    {
        return $this->_getData('page_name');
    }

    /**
     * Render regular page tracking javascript code
     * The custom "page name" may be set from layout or somewhere else. It must start from slash.
     *
     * @param string $accountId
     * @return string
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/method-reference#set
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/method-reference#gaObjectMethods
     */
    public function getPageTrackingCode($accountId)
    {
        $pageName = trim($this->getPageName());
        $optPageURL = '';
        if ($pageName && substr($pageName, 0, 1) == '/' && strlen($pageName) > 1) {
            $optPageURL = ", '" . $this->escapeHtmlAttr($pageName, false) . "'";
        }

        $anonymizeIp = "";
        if ($this->_googleAnalyticsData->isAnonymizedIpActive()) {
            $anonymizeIp = "\nga('set', 'anonymizeIp', true);";
        }

        return "\nga('create', '" . $this->escapeHtmlAttr($accountId, false)
           . "', 'auto');{$anonymizeIp}\nga('send', 'pageview'{$optPageURL});\n";
    }

    /**
     * Render information about specified orders and their items
     *
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#checkout-options
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#measuring-transactions
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#transaction
     *
     * @return string|void
     */
    public function getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $collection = $this->_salesOrderCollection->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);
        $result = [];

        $result[] = "ga('require', 'ec', 'ec.js');";

        foreach ($collection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf(
                    "ga('ec:addProduct', {
                        'id': '%s',
                        'name': '%s',
                        'price': '%s',
                        'quantity': %s
                    });",
                    $this->escapeJs($item->getSku()),
                    $this->escapeJs($item->getName()),
                    $item->getBasePrice(),
                    $item->getQtyOrdered()
                );
            }

            $result[] = sprintf(
                "ga('ec:setAction', 'purchase', {
                    'id': '%s',
                    'affiliation': '%s',
                    'revenue': '%s',
                    'tax': '%s',
                    'shipping': '%s'
                });",
                $order->getIncrementId(),
                $this->escapeJs($this->_storeManager->getStore()->getFrontendName()),
                $order->getBaseGrandTotal(),
                $order->getBaseTaxAmount(),
                $order->getBaseShippingAmount()
            );

            $result[] = "ga('send', 'pageview');";
        }
        return implode("\n", $result);
    }

    /**
     * Render GA tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_googleAnalyticsData->isGoogleAnalyticsAvailable()) {
            return '';
        }

        return parent::_toHtml();
    }
}
