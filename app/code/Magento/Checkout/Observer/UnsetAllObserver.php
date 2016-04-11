<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

class UnsetAllObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(\Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @codeCoverageIgnore
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->checkoutSession->clearQuote()->clearStorage();
    }
}
