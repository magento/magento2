<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\GoogleGtag\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Google Analytics module observer
 */
class SetGoogleAnalyticsOnOrderSuccessPageViewObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_googleGtagData = null;

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param LayoutInterface $layout
     * @param Data $googleGtagData
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LayoutInterface $layout,
        Data $googleGtagData
    ) {
        $this->_googleGtagData = $googleGtagData;
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $block = $this->_layout->getBlock('google_gtag_analytics');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }
}
