<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer\Backend;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CustomerQuote
 */
class CustomerQuoteObserver implements ObserverInterface
{
    /**
     * @var ShareConfig
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ShareConfig $config
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ShareConfig $config,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getEvent()->getCustomerDataObject();
        try {
            $quote = $this->quoteRepository->getForCustomer($customer->getId());
            if ($customer->getGroupId() !== $quote->getCustomerGroupId()) {
                /**
                 * It is needed to process customer's quotes for all websites
                 * if customer accounts are shared between all of them
                 */
                /** @var $websites \Magento\Store\Model\Website[] */
                $websites = $this->config->isWebsiteScope()
                    ? [$this->storeManager->getWebsite($customer->getWebsiteId())]
                    : $this->storeManager->getWebsites();

                foreach ($websites as $website) {
                    $quote->setWebsite($website);
                    $quote->setCustomerGroupId($customer->getGroupId());
                    $quote->collectTotals();
                    $this->quoteRepository->save($quote);
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }
    }
}
