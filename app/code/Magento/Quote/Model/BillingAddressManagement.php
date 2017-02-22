<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface as Logger;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/** Quote billing address write service object. */
class BillingAddressManagement implements BillingAddressManagementInterface
{
    /**
     * Validator.
     *
     * @var QuoteAddressValidator
     */
    protected $addressValidator;

    /**
     * Logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Constructs a quote billing address service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository Quote repository.
     * @param QuoteAddressValidator $addressValidator Address validator.
     * @param Logger $logger Logger.
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        Logger $logger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address, $useForShipping = false)
    {
        $quote = $this->quoteRepository->getActive($cartId);

        $this->addressValidator->validate($address);
        $customerAddressId = $address->getCustomerAddressId();
        $shippingAddress = null;
        $addressData = [];

        if ($useForShipping) {
            $shippingAddress = $address;
        }
        $saveInAddressBook = $address->getSaveInAddressBook() ? 1 : 0;
        if ($customerAddressId) {
            try {
                $addressData = $this->addressRepository->getById($customerAddressId);
            } catch (NoSuchEntityException $e) {
                // do nothing if customer is not found by id
            }
            $address = $quote->getBillingAddress()->importCustomerAddressData($addressData);
            if ($useForShipping) {
                $shippingAddress = $quote->getShippingAddress()->importCustomerAddressData($addressData);
                $shippingAddress->setSaveInAddressBook($saveInAddressBook);
            }
        } elseif ($quote->getCustomerId()) {
            $address->setEmail($quote->getCustomerEmail());
        }
        $address->setSaveInAddressBook($saveInAddressBook);
        $quote->setBillingAddress($address);
        if ($useForShipping) {
            $shippingAddress->setSameAsBilling(1);
            $shippingAddress->setCollectShippingRates(true);
            $quote->setShippingAddress($shippingAddress);
        }
        $quote->setDataChanges(true);
        $quote->collectTotals();
        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(__('Unable to save address. Please, check input data.'));
        }
        return $quote->getBillingAddress()->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        $cart = $this->quoteRepository->getActive($cartId);
        return $cart->getBillingAddress();
    }
}
