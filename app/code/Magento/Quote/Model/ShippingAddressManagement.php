<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.0.0
 */
class ShippingAddressManagement implements \Magento\Quote\Model\ShippingAddressManagementInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     * @since 2.0.0
     */
    protected $quoteRepository;

    /**
     * Logger.
     *
     * @var Logger
     * @since 2.0.0
     */
    protected $logger;

    /**
     * Validator.
     *
     * @var QuoteAddressValidator
     * @since 2.0.0
     */
    protected $addressValidator;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     * @since 2.0.0
     */
    protected $addressRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var Quote\TotalsCollector
     * @since 2.0.0
     */
    protected $totalsCollector;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param QuoteAddressValidator $addressValidator
     * @param Logger $logger
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Quote\TotalsCollector $totalsCollector
     *
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
}
