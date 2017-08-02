<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class EmulateQuote
 * @since 2.0.0
 */
class EmulateQuoteObserver implements ObserverInterface
{
    /**
     * Customer account service
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.0.0
     */
    protected $_persistentSession = null;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.0.0
     */
    protected $_persistentData = null;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_persistentSession = $persistentSession;
        $this->_persistentData = $persistentData;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Emulate quote by persistent data
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $stopActions = ['persistent_index_saveMethod', 'customer_account_createpost'];

        if (!$this->_persistentData->canProcess($observer)
            || !$this->_persistentSession->isPersistent()
            || $this->_customerSession->isLoggedIn()
        ) {
            return;
        }

        $actionName = $observer->getEvent()->getRequest()->getFullActionName();

        if (in_array($actionName, $stopActions)) {
            return;
        }

        if ($this->_persistentData->isShoppingCartPersist()) {
            $this->_checkoutSession->setCustomerData(
                $this->customerRepository->getById($this->_persistentSession->getSession()->getCustomerId())
            );
            if (!$this->_checkoutSession->hasQuote()) {
                $this->_checkoutSession->getQuote();
            }
        }
    }
}
