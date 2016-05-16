<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Persistent Session Observer
 */
class SetRememberMeCheckedStatusObserver implements ObserverInterface
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $_persistentData = null;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_persistentData = $persistentData;
        $this->_persistentSession = $persistentSession;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Set Checked status of "Remember Me"
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->_persistentData->canProcess($observer)
            || !$this->_persistentData->isEnabled()
            || !$this->_persistentData->isRememberMeEnabled()
        ) {
            return;
        }

        /** @var $controllerAction \Magento\Framework\App\RequestInterface */
        $request = $observer->getEvent()->getRequest();
        if ($request) {
            $rememberMeCheckbox = $request->getPost('persistent_remember_me');
            $this->_persistentSession->setRememberMeChecked((bool)$rememberMeCheckbox);
            if ($request->getFullActionName() == 'checkout_onepage_saveBilling' ||
                $request->getFullActionName() == 'customer_account_createpost'
            ) {
                $this->_checkoutSession->setRememberMeChecked((bool)$rememberMeCheckbox);
            }
        }
    }
}
