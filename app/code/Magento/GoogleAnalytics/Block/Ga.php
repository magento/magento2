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
     * @return \Magento\GoogleAnalaytics\Helper\Data
     */
    public function getHelper()
    {
        return $this->_googleAnalyticsData;
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
     * @link https://developers.google.com/analytics/devguides/collection/gtagjs
     * @link https://developers.google.com/analytics/devguides/collection/ga4
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
     * @link https://developers.google.com/analytics/devguides/collection/ga4/ecommerce#purchase
     * @link https://developers.google.com/gtagjs/reference/ga4-events#purchase
     * @link https://developers.google.com/analytics/devguides/collection/gtagjs/enhanced-ecommerce#product-data
     * @link https://developers.google.com/gtagjs/reference/event#purchase
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
        $isUniversalAnalyticsAccount = $this->_googleAnalyticsData->isUniversalAnalyticsAccount();

        foreach ($collection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $result['products'][] = [
                    $isUniversalAnalyticsAccount ? 'id' : 'item_id' => $this->escapeJsQuote($item->getSku()),
                    $isUniversalAnalyticsAccount ? 'name' : 'item_name' =>  $this->escapeJsQuote($item->getName()),
                    'price' => $this->_googleAnalyticsData->formatToDec($item->getPrice()),
                    'quantity' => (int)$item->getQtyOrdered(),
                ];
            }
            $result['orders'][] = [
                'transaction_id' =>  $order->getIncrementId(),
                'affiliation' => $this->escapeJsQuote($this->_storeManager->getStore()->getFrontendName()),
                'value' => $this->_googleAnalyticsData->formatToDec($order->getGrandTotal()),
                'tax' => $this->_googleAnalyticsData->formatToDec($order->getTaxAmount()),
                'shipping' => $this->_googleAnalyticsData->formatToDec($order->getShippingAmount()),
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
