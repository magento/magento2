<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
<<<<<<< HEAD
 * Observer for a work with persistent data.
=======
 * Class SetCheckoutSessionPersistentDataObserver
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class SetCheckoutSessionPersistentDataObserver implements ObserverInterface
{
    /**
<<<<<<< HEAD
     * Persistent session.
=======
     * Persistent session
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @var \Magento\Persistent\Helper\Session
     */
    private $persistentSession = null;

    /**
<<<<<<< HEAD
     * Customer session.
=======
     * Customer session
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
<<<<<<< HEAD
     * Persistent data.
=======
     * Persistent data
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @var \Magento\Persistent\Helper\Data
     */
    private $persistentData = null;

    /**
<<<<<<< HEAD
     * Customer Repository.
=======
     * Customer Repository
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository = null;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->persistentSession = $persistentSession;
        $this->customerSession = $customerSession;
        $this->persistentData = $persistentData;
        $this->customerRepository = $customerRepository;
    }

    /**
<<<<<<< HEAD
     * Pass customer data from persistent session to checkout session and set quote to be loaded even if not active.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
=======
     * Pass customer data from persistent session to checkout session and set quote to be loaded even if not active
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $checkoutSession \Magento\Checkout\Model\Session */
        $checkoutSession = $observer->getEvent()->getData('checkout_session');
        if ($this->persistentData->isShoppingCartPersist() && $this->persistentSession->isPersistent()) {
            $checkoutSession->setCustomerData(
                $this->customerRepository->getById($this->persistentSession->getSession()->getCustomerId())
            );
        }
        if (!(($this->persistentSession->isPersistent() && !$this->customerSession->isLoggedIn())
            && !$this->persistentData->isShoppingCartPersist()
        )) {
            return;
        }
        if ($checkoutSession) {
            $checkoutSession->setLoadInactive();
        }
    }
}
