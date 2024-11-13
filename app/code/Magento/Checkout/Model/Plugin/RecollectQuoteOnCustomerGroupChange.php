<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Recollect quote when customer group updated through API
 */
class RecollectQuoteOnCustomerGroupChange
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * Initialize Constructor
     *
     * @param CartRepositoryInterface $cartRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface|null $scopeConfig
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CustomerRepositoryInterface $customerRepository,
        ?ScopeConfigInterface $scopeConfig = null,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->cartRepository = $cartRepository;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()
            ->get(ScopeConfigInterface::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
    }

    /**
     * Plugin around create customer that triggers to update and recollect all customer cart
     *
     * @param CustomerResource $subject
     * @param callable $proceed
     * @param AbstractModel $customer
     * @return CustomerResource
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        CustomerResource $subject,
        callable $proceed,
        AbstractModel $customer
    ): CustomerResource {
        $customerId = $customer->getId() ?: $customer->getEntityId();
        /** @var Customer $customer */
        if ($customerId && empty($customer->getTaxvat())) {
            try {
                $prevCustomerData = $this->customerRepository->getById($customerId);
                $previousCustomerData = $prevCustomerData->__toArray();
            } catch (NoSuchEntityException $e) {
                $previousCustomerData = [];
            }
        }

        $result = $proceed($customer);

        if (!empty($previousCustomerData)
            && $previousCustomerData['group_id'] !== null
            && $previousCustomerData['group_id'] != $customer->getGroupId()
            && empty($previousCustomerData['taxvat'])
        ) {
            try {
                $customerAccountShareScope = $this->scopeConfig->getValue(
                    Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
                if ($customerAccountShareScope === Share::SHARE_WEBSITE) {
                    /** @var Quote $quote */
                    $quote = $this->cartRepository->getActiveForCustomer($customer->getId());
                    $quote->setCustomerGroupId($customer->getGroupId());
                    $quote->collectTotals();
                    $this->cartRepository->save($quote);
                } else {
                    $this->collectTotalsForCustomerGlobalScope($customer);
                }
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (NoSuchEntityException $e) {
                //no active cart for customer
            }
        }

        return $result;
    }

    /**
     * Re-collect totals for customer share account global scope
     *
     * @param Customer|AbstractModel $customer
     * @return void
     * @throws NoSuchEntityException
     */
    private function collectTotalsForCustomerGlobalScope(Customer|AbstractModel $customer): void
    {
        $allStores = $this->storeManager->getStores();
        foreach ($allStores as $store) {
            /** @var Quote $quote */
            $quote = $this->cartRepository->getActiveForCustomer($customer->getId(), [$store->getId()]);
            if ($quote) {
                $quote->setCustomerGroupId($customer->getGroupId());
                $quote->collectTotals();
                $this->cartRepository->save($quote);
            }
        }
    }
}
