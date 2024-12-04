<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleAnalytics\Block;

use Magento\Framework\App\ObjectManager;

/**
 * GoogleAnalytics Page Block
 *
 * @api
 * @since 100.0.2
 */
class Ga extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\GoogleAnalytics\Helper\Data
     */
    protected $_googleAnalyticsData = null;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_salesOrderCollection;

    /**
     * @var \Magento\Cookie\Helper\Cookie
     */
    private $cookieHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection
     * @param \Magento\GoogleAnalytics\Helper\Data $googleAnalyticsData
     * @param array $data
     * @param \Magento\Cookie\Helper\Cookie|null $cookieHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection,
        \Magento\GoogleAnalytics\Helper\Data $googleAnalyticsData,
        array $data = [],
        \Magento\Cookie\Helper\Cookie $cookieHelper = null
    ) {
        $this->_googleAnalyticsData = $googleAnalyticsData;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->cookieHelper = $cookieHelper ?: ObjectManager::getInstance()->get(\Magento\Cookie\Helper\Cookie::class);
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
     * Render regular page tracking javascript code.
     *
     * The custom "page name" may be set from layout or somewhere else. It must start from slash.
     *
     * @param string $accountId
     * @return string
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/method-reference#set
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/method-reference#gaObjectMethods
     * @deprecated 100.2.0 please use getPageTrackingData method
     * @see getPageTrackingData method
     */
    public function getPageTrackingCode($accountId)
    {
        $anonymizeIp = "";
        if ($this->_googleAnalyticsData->isAnonymizedIpActive()) {
            $anonymizeIp = "\nga('set', 'anonymizeIp', true);";
        }

        return "\nga('create', '" . $this->escapeHtmlAttr($accountId, false)
           . "', 'auto');{$anonymizeIp}\nga('send', 'pageview'{$this->getOptPageUrl()});\n";
    }

    /**
     * Render information about specified orders and their items
     *
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#checkout-options
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#measuring-transactions
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#transaction
     *
     * @return string|void
     * @deprecated 100.2.0 please use getOrdersTrackingData method
     * @see getOrdersTrackingData method
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
            $result[] = "ga('set', 'currencyCode', '" . $order->getOrderCurrencyCode() . "');";
            foreach ($order->getAllVisibleItems() as $item) {
                $quantity = $item->getQtyOrdered() * 1;
                $format = fmod($quantity, 1) !== 0.00 ? '%.2f' : '%d';
                $result[] = sprintf(
                    "ga('ec:addProduct', {
                        'id': '%s',
                        'name': '%s',
                        'price': %.2f,
                        'quantity': $format
                    });",
                    $this->escapeJsQuote($item->getSku()),
                    $this->escapeJsQuote($item->getName()),
                    (float)$item->getPrice(),
                    $quantity
                );
            }

            $result[] = sprintf(
                "ga('ec:setAction', 'purchase', {
                    'id': '%s',
                    'affiliation': '%s',
                    'revenue': %.2f,
                    'tax': %.2f,
                    'shipping': %.2f
                });",
                $order->getIncrementId(),
                $this->escapeJsQuote($this->_storeManager->getStore()->getFrontendName()),
                (float)$order->getGrandTotal(),
                (float)$order->getTaxAmount(),
                (float)$order->getShippingAmount(),
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

    /**
     * Return cookie restriction mode value.
     *
     * @return bool
     * @since 100.2.0
     */
    public function isCookieRestrictionModeEnabled()
    {
        return $this->cookieHelper->isCookieRestrictionModeEnabled();
    }

    /**
     * Return current website id.
     *
     * @return int
     * @since 100.2.0
     */
    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getWebsite()->getId();
    }

    /**
     * Return information about page for GA tracking
     *
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/method-reference#set
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/method-reference#gaObjectMethods
     *
     * @param string $accountId
     * @return array
     * @since 100.2.0
     */
    public function getPageTrackingData($accountId)
    {
        return [
            'optPageUrl' => $this->getOptPageUrl(),
            'isAnonymizedIpActive' => $this->_googleAnalyticsData->isAnonymizedIpActive(),
            'accountId' => $this->escapeHtmlAttr($accountId, false)
        ];
    }

    /**
     * Return information about order and items for GA tracking.
     *
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#checkout-options
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#measuring-transactions
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#transaction
     *
     * @return array
     * @since 100.2.0
     */
    public function getOrdersTrackingData()
    {
        $result = [];
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return $result;
        }

        $collection = $this->_salesOrderCollection->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        foreach ($collection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $quantity = $item->getQtyOrdered() * 1;
                $result['products'][] = [
                    'id' => $this->escapeJsQuote($item->getSku()),
                    'name' =>  $this->escapeJsQuote($item->getName()),
                    'price' => (float)$item->getPrice(),
                    'quantity' => $quantity,
                ];
            }
            $result['orders'][] = [
                'id' =>  $order->getIncrementId(),
                'affiliation' => $this->escapeJsQuote($this->_storeManager->getStore()->getFrontendName()),
                'revenue' => (float)$order->getGrandTotal(),
                'tax' => (float)$order->getTaxAmount(),
                'shipping' => (float)$order->getShippingAmount(),
            ];
            $result['currency'] = $order->getOrderCurrencyCode();
        }
        return $result;
    }

    /**
     * Return page url for tracking.
     *
     * @return string
     */
    private function getOptPageUrl()
    {
        $optPageURL = '';
        $pageName = $this->getPageName() !== null ? trim($this->getPageName()) : '';
        if ($pageName && substr($pageName, 0, 1) === '/' && strlen($pageName) > 1) {
            $optPageURL = ", '" . $this->escapeHtmlAttr($pageName, false) . "'";
        }
        return $optPageURL;
    }
}
