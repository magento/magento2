<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteAddressValidator;

class ShippingAddressPersister
{
    /**
     * @var QuoteAddressValidator
     */
    private $addressValidator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param QuoteAddressValidator $addressValidator
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        QuoteAddressValidator $addressValidator,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->addressValidator = $addressValidator;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Save address for shipping.
     *
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @return void
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function save(CartInterface $quote, AddressInterface $address): void
    {
        $this->addressValidator->validateForCart($quote, $address);
        $customerAddressId = $address->getCustomerAddressId();

        $saveInAddressBook = $address->getSaveInAddressBook() ? 1 : 0;
        if ($customerAddressId) {
            try {
                $addressData = $this->addressRepository->getById($customerAddressId);
                $address = $quote->getShippingAddress()->importCustomerAddressData($addressData);
            } catch (NoSuchEntityException $e) {
                $address->setCustomerAddressId(null);
            }
        } elseif ($quote->getCustomerId()) {
            $address->setEmail($quote->getCustomerEmail());
        }
        $address->setSaveInAddressBook($saveInAddressBook);
        $quote->setShippingAddress($address);
    }
}
