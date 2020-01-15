<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address;
use Psr\Log\LoggerInterface;

/**
 * Quote shipping address write service object.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingAddressManagement implements ShippingAddressManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteAddressValidator
     */
    private $addressValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteAddressValidator $addressValidator
     * @param LoggerInterface $logger
     * @param AddressRepositoryInterface $addressRepository
     *
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        LoggerInterface $logger,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function assign($cartId, AddressInterface $address)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                __('The Cart includes virtual product(s) only, so a shipping address is not used.')
            );
        }

        $saveInAddressBook = $address->getSaveInAddressBook() ? 1 : 0;
        $sameAsBilling = $address->getSameAsBilling() ? 1 : 0;
        $customerAddressId = $address->getCustomerAddressId();
        $this->addressValidator->validateForCart($quote, $address);
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
            throw new InputException(__('The address failed to save. Verify the address and try again.'));
        }
        return $quote->getShippingAddress()->getId();
    }

    /**
     * @inheritDoc
     */
    public function get($cartId)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                __('The Cart includes virtual product(s) only, so a shipping address is not used.')
            );
        }
        /** @var Address $address */
        return $quote->getShippingAddress();
    }
}
