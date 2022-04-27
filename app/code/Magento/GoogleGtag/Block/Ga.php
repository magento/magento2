<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Block;

use Magento\Cookie\Helper\Cookie;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\GoogleGtag\Helper\GtagConfiguration;
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
     * @var GtagConfiguration
     */
    private $googleGtagConfig;

    /**
     * @var CollectionFactory
     */
    private $salesOrderCollection;

    /**
     * @var Cookie
     */
    private $cookieHelper;

    /**
     * @param Context $context
     * @param CollectionFactory $salesOrderCollection
     * @param GtagConfiguration $googleGtagConfig
     * @param array $data
     * @param Cookie $cookieHelper
     */
    public function __construct(
        Context $context,
        CollectionFactory $salesOrderCollection,
        GtagConfiguration $googleGtagConfig,
        Cookie $cookieHelper,
        array $data = []
    ) {
        $this->googleGtagConfig = $googleGtagConfig;
        $this->salesOrderCollection = $salesOrderCollection;
        $this->cookieHelper = $cookieHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get config
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path): string
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get helper
     *
     * @return GtagConfiguration
     */
    public function getHelper(): GtagConfiguration
    {
        return $this->googleGtagConfig;
    }

    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName(): ?string
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
        if (!$this->googleGtagConfig->isGoogleAnalyticsAvailable()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Return cookie restriction mode value.
     *
     * @return bool
     */
    public function isCookieRestrictionModeEnabled(): bool
    {
        return (bool) $this->cookieHelper->isCookieRestrictionModeEnabled();
    }

    /**
     * Return current website id.
     *
     * @return int
     */
    public function getCurrentWebsiteId(): int
    {
        return (int) $this->_storeManager->getWebsite()->getId();
    }

    /**
     * Return information about page for GA tracking
     *
     * @link https://developers.google.com/analytics/devguides/collection/gtagjs
     * @link https://developers.google.com/analytics/devguides/collection/ga4
     *
     * @param string $measurementId
     * @return array
     */
    public function getPageTrackingData($measurementId): array
    {
        return [
            'optPageUrl' => $this->getOptPageUrl(),
            'measurementId' => $this->escapeHtmlAttr($measurementId, false)
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
    public function getOrdersTrackingData(): array
    {
        $result = [];
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return $result;
        }

        $collection = $this->salesOrderCollection->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        foreach ($collection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $result['products'][] = [
                    'item_id' => $this->escapeJsQuote($item->getSku()),
                    'item_name' =>  $this->escapeJsQuote($item->getName()),
                    'price' => $this->googleGtagConfig->formatToDec((float) $item->getPrice()),
                    'quantity' => (int)$item->getQtyOrdered(),
                ];
            }
            $result['orders'][] = [
                'transaction_id' =>  $order->getIncrementId(),
                'affiliation' => $this->escapeJsQuote($this->_storeManager->getStore()->getFrontendName()),
                'value' => $this->googleGtagConfig->formatToDec((float) $order->getGrandTotal()),
                'tax' => $this->googleGtagConfig->formatToDec((float) $order->getTaxAmount()),
                'shipping' => $this->googleGtagConfig->formatToDec((float) $order->getShippingAmount()),
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
    private function getOptPageUrl(): string
    {
        $optPageURL = '';
        $pageName = $this->getPageName() !== null ? trim($this->getPageName()) : '';
        if ($pageName && substr($pageName, 0, 1) === '/' && strlen($pageName) > 1) {
            $optPageURL = ", '" . $this->escapeHtmlAttr($pageName, false) . "'";
        }
        return $optPageURL;
    }
}
