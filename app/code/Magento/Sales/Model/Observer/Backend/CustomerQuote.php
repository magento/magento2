<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer\Backend;

/**
 * Class CustomerQuote
 */
class CustomerQuote
{
    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    protected $_config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Config\Share $config
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Config\Share $config,
        \Magento\Sales\Model\QuoteRepository $quoteRepository
    ) {
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function dispatch(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getEvent()->getCustomerDataObject();
        /** @var \Magento\Customer\Api\Data\CustomerInterface $origCustomer */
        $origCustomer = $observer->getEvent()->getOrigCustomerDataObject();
        if ($customer->getGroupId() !== $origCustomer->getGroupId()) {
            /**
             * It is needed to process customer's quotes for all websites
             * if customer accounts are shared between all of them
             */
            /** @var $websites \Magento\Store\Model\Website[] */
            $websites = $this->_config->isWebsiteScope()
                ? [$this->_storeManager->getWebsite($customer->getWebsiteId())]
                : $this->_storeManager->getWebsites();

            foreach ($websites as $website) {
                try {
                    $quote = $this->quoteRepository->getForCustomer($customer->getId());
                    $quote->setWebsite($website);
                    $quote->setCustomerGroupId($customer->getGroupId());
                    $quote->collectTotals();
                    $this->quoteRepository->save($quote);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                }
            }
        }
    }
}
