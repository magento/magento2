<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Quote shipping address write service object.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingAddressManagement implements \Magento\Quote\Model\ShippingAddressManagementInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Validator.
     *
     * @var QuoteAddressValidator
     */
    protected $addressValidator;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage
     */
    private $minimumAmountErrorMessage;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param QuoteAddressValidator $addressValidator
     * @param Logger $logger
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Quote\TotalsCollector $totalsCollector
     *
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        Logger $logger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->scopeConfig = $scopeConfig;
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                __('Cart contains virtual product(s) only. Shipping address is not applicable.')
            );
        }

        $saveInAddressBook = $address->getSaveInAddressBook() ? 1 : 0;
        $sameAsBilling = $address->getSameAsBilling() ? 1 : 0;
        $customerAddressId = $address->getCustomerAddressId();
        $this->addressValidator->validate($address);
        $quote->setShippingAddress($address);
        $address = $quote->getShippingAddress();

        if ($customerAddressId === null) {
            $address->setCustomerAddressId(null);
        }

        if ($customerAddressId) {
            $addressData = $this->addressRepository->getById($customerAddressId);
            $address = $quote->getShippingAddress()->importCustomerAddressData($addressData);
        } elseif ($quote->getCustomerId()) {
            $address->setEmail($quote->getCustomerEmail());
        }
        $address->setSameAsBilling($sameAsBilling);
        $address->setSaveInAddressBook($saveInAddressBook);
        $address->setCollectShippingRates(true);

        if (!$quote->validateMinimumAmount($quote->getIsMultiShipping())) {
            throw new InputException($this->getMinimumAmountErrorMessage()->getMessage());
        }

        try {
            $address->save();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(__('Unable to save address. Please check input data.'));
        }
        return $quote->getShippingAddress()->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                __('Cart contains virtual product(s) only. Shipping address is not applicable.')
            );
        }
        /** @var \Magento\Quote\Model\Quote\Address $address */
        return $quote->getShippingAddress();
    }

    /**
     * @return \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage
     * @deprecated
     */
    private function getMinimumAmountErrorMessage()
    {
        if ($this->minimumAmountErrorMessage === null) {
            $objectManager = ObjectManager::getInstance();
            $this->minimumAmountErrorMessage = $objectManager->get(
                \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage::class
            );
        }
        return $this->minimumAmountErrorMessage;
    }
}
