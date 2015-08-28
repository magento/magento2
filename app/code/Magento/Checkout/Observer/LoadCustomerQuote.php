<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Observer;

class LoadCustomerQuote
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @return void
     */
    public function invoke()
    {
        try {
            $this->checkoutSession->loadCustomerQuote();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Load customer quote error'));
        }
    }
}
