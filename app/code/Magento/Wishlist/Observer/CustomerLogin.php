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
 */
class CustomerLogin implements ObserverInterface
{
    /**
     * @param Data $wishlistData
     */
    public function __construct(
        protected readonly Data $wishlistData
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->wishlistData->calculate();
    }
}
