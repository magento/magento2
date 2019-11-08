<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Save Address to Customer Address Book.
 */
class SaveQuoteAddressToCustomerAddressBook
{
    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @param AddressInterfaceFactory $addressFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param RegionInterfaceFactory $regionFactory
     */
    public function __construct(
        AddressInterfaceFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        RegionInterfaceFactory $regionFactory
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Save Address to Customer Address Book.
     *
     * @param QuoteAddress $quoteAddress
     * @param int $customerId
     *
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(QuoteAddress $quoteAddress, int $customerId): void
    {
        try {
            /** @var AddressInterface $customerAddress */
            $customerAddress = $this->addressFactory->create();
            $customerAddress->setFirstname($quoteAddress->getFirstname())
                ->setLastname($quoteAddress->getLastname())
                ->setMiddlename($quoteAddress->getMiddlename())
                ->setPrefix($quoteAddress->getPrefix())
                ->setSuffix($quoteAddress->getSuffix())
                ->setVatId($quoteAddress->getVatId())
                ->setCountryId($quoteAddress->getCountryId())
                ->setCompany($quoteAddress->getCompany())
                ->setRegionId($quoteAddress->getRegionId())
                ->setFax($quoteAddress->getFax())
                ->setCity($quoteAddress->getCity())
                ->setPostcode($quoteAddress->getPostcode())
                ->setStreet($quoteAddress->getStreet())
                ->setTelephone($quoteAddress->getTelephone())
                ->setCustomerId($customerId);

            /** @var RegionInterface $region */
            $region = $this->regionFactory->create();
            $region->setRegionCode($quoteAddress->getRegionCode())
                ->setRegion($quoteAddress->getRegion())
                ->setRegionId($quoteAddress->getRegionId());
            $customerAddress->setRegion($region);

            $this->addressRepository->save($customerAddress);
        } catch (InputException $inputException) {
            $graphQlInputException = new GraphQlInputException(__($inputException->getMessage()));
            $errors = $inputException->getErrors();
            foreach ($errors as $error) {
                $graphQlInputException->addError(new GraphQlInputException(__($error->getMessage())));
            }
            throw $graphQlInputException;
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()), $exception);
        }
    }
}
