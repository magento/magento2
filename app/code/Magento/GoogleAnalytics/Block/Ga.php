<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleAnalytics\Block;

/**
 * GoogleAnalytics Page Block
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
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory
     */
    protected $_salesOrderCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\Resource\Order\CollectionFactory $salesOrderCollection
     * @param \Magento\GoogleAnalytics\Helper\Data $googleAnalyticsData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Resource\Order\CollectionFactory $salesOrderCollection,
        \Magento\GoogleAnalytics\Helper\Data $googleAnalyticsData,
        array $data = array()
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
            $optPageURL = ", '{$this->escapeJsQuote($pageName)}'";
        }

        return "\nga('create', '{$this->escapeJsQuote(
            $accountId
        )}', 'auto');\nga('send', 'pageview'{$optPageURL});\n";
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
        $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = [];

        $result[] = "ga('require', 'ec', 'ec.js');";
        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }

            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf(
                    "ga('ec:addProduct', {
                        'id': '%s',
                        'name': '%s',
                        'price': '%s',
                        'quantity': %s
                    });",
                    $this->escapeJsQuote($item->getSku()),
                    $this->escapeJsQuote($item->getName()),
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
                $this->escapeJsQuote($this->_storeManager->getStore()->getFrontendName()),
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
