<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

/**
 * Class CustomerLogout
 * @package Magento\Wishlist\Observer
 */
class CustomerLogout implements ObserverInterface
{
    /** @var Session */
    protected $customerSession;

    /**
     * @param Session $customerSession
     */
    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Customer logout processing
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->customerSession->setWishlistItemCount(0);
    }
}
