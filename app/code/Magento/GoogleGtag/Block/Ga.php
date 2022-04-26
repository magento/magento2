<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Block;

use Magento\Cookie\Helper\Cookie;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\GoogleGtag\Helper\Data;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * GoogleAnalytics Page Block
 *
 * @api
 */
class Ga extends Template
{
    /**
     * @var Data
     */
    protected $_googleGtagData = null;

    /**
     * @var CollectionFactory
     */
    protected $_salesOrderCollection;

    /**
     * @var Cookie
     */
    private $cookieHelper;

    /**
     * @param Context $context
     * @param CollectionFactory $salesOrderCollection
     * @param Data $googleGtagData
     * @param array $data
     * @param Cookie|null $cookieHelper
     */
    public function __construct(
        Context $context,
        CollectionFactory $salesOrderCollection,
        Data $googleGtagData,
        array $data = [],
        Cookie $cookieHelper = null
    ) {
        $this->_googleGtagData = $googleGtagData;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->cookieHelper = $cookieHelper ?: ObjectManager::getInstance()->get(Cookie::class);
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
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get helper
     *
     * @return Data|null
     */
    public function getHelper()
    {
        return $this->_googleGtagData;
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
        if (!$this->_googleGtagData->isGoogleAnalyticsAvailable()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Return cookie restriction mode value.
     *
     * @return bool
     */
    public function isCookieRestrictionModeEnabled()
    {
        return $this->cookieHelper->isCookieRestrictionModeEnabled();
    }

    /**
     * Return current website id.
     *
     * @return int
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
     */
    public function getPageTrackingData($accountId)
    {
        return [
            'optPageUrl' => $this->getOptPageUrl(),
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

        foreach ($collection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $result['products'][] = [
                    'item_id' => $this->escapeJsQuote($item->getSku()),
                    'item_name' =>  $this->escapeJsQuote($item->getName()),
                    'price' => $this->_googleGtagData->formatToDec((float) $item->getPrice()),
                    'quantity' => (int)$item->getQtyOrdered(),
                ];
            }
            $result['orders'][] = [
                'transaction_id' =>  $order->getIncrementId(),
                'affiliation' => $this->escapeJsQuote($this->_storeManager->getStore()->getFrontendName()),
                'value' => $this->_googleGtagData->formatToDec((float) $order->getGrandTotal()),
                'tax' => $this->_googleGtagData->formatToDec((float) $order->getTaxAmount()),
                'shipping' => $this->_googleGtagData->formatToDec((float) $order->getShippingAmount()),
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
