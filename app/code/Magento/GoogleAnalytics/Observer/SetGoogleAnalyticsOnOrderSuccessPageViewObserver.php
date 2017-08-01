<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAnalytics\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Google Analytics module observer
 *
 * @since 2.0.0
 */
class SetGoogleAnalyticsOnOrderSuccessPageViewObserver implements ObserverInterface
{
    /**
     * Google analytics data
     *
     * @var \Magento\GoogleAnalytics\Helper\Data
     * @since 2.0.0
     */
    protected $_googleAnalyticsData = null;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    protected $_layout;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\GoogleAnalytics\Helper\Data $googleAnalyticsData
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\GoogleAnalytics\Helper\Data $googleAnalyticsData
    ) {
        $this->_googleAnalyticsData = $googleAnalyticsData;
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $block = $this->_layout->getBlock('google_analytics');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }
}
