<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Wishlist\Helper\Data;

/**
 * Class CustomerLogin
 * @package Magento\Wishlist\Observer
 * @since 2.0.0
 */
class CustomerLogin implements ObserverInterface
{
    /**
     * @var \Magento\Wishlist\Helper\Data
     * @since 2.0.0
     */
    protected $wishlistData;

    /**
     * @param Data $wishlistData
     * @since 2.0.0
     */
    public function __construct(Data $wishlistData)
    {
        $this->wishlistData = $wishlistData;
    }

    /**
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        $this->wishlistData->calculate();
    }
}
