<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    /** @var Data */
    protected $wishlistData;

    /**
     * @param Data $wishlistData
     */
    public function __construct(Data $wishlistData)
    {
        $this->wishlistData = $wishlistData;
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
