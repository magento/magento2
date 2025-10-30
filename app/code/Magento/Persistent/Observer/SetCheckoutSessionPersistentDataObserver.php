<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\SessionFactory;

/**
 * Event SetCheckoutSessionPersistentData
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SetCheckoutSessionPersistentDataObserver implements ObserverInterface
{
    /**
     * Persistent session helper
     *
     * @var Session
     */
    private $persistentSession = null;

    /**
     * Customer model session
     *
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Persistent helper
     *
     * @var Data
     */
    private $persistentData = null;

    /**
     * Customer Repository class
     *
     * @var CustomerRepositoryInterface
     */
    private $customerRepository = null;

    /**
     * Session factory class
     *
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @param Session $persistentSession
     * @param CustomerSession $customerSession
     * @param Data $persistentData
     * @param CustomerRepositoryInterface $customerRepository
     * @param SessionFactory $sessionFactory
     */
    public function __construct(
        Session $persistentSession,
        CustomerSession $customerSession,
        Data $persistentData,
        CustomerRepositoryInterface $customerRepository,
        ?SessionFactory $sessionFactory = null
    ) {
        $this->persistentSession = $persistentSession;
        $this->customerSession = $customerSession;
        $this->persistentData = $persistentData;
        $this->customerRepository = $customerRepository;
        $this->sessionFactory = $sessionFactory ?? ObjectManager::getInstance()->get(SessionFactory::class);
    }

    /**
     * Pass customer data from persistent session to checkout session and set quote to be loaded even if not active
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $checkoutSession \Magento\Checkout\Model\Session */
        $checkoutSession = $observer->getEvent()->getData('checkout_session');
        if ($this->persistentData->isShoppingCartPersist() && $this->persistentSession->isPersistent()) {
            if (!$this->customerSession->isLoggedIn() && $this->persistentData->getClearOnLogout()) {
                $this->sessionFactory->create()->removePersistentCookie();
                // Unset persistent session
                $this->persistentSession->setSession(null);
                return;
            }
            $checkoutSession->setCustomerData(
                $this->customerRepository->getById($this->persistentSession->getSession()->getCustomerId())
            );
        }
        if (!(
            ($this->persistentSession->isPersistent() && !$this->customerSession->isLoggedIn())
            && !$this->persistentData->isShoppingCartPersist()
        )) {
            return;
        }
        if ($checkoutSession) {
            $checkoutSession->setLoadInactive();
        }
    }
}
