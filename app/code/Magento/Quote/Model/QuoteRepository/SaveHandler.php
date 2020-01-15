<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\QuoteRepository;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address\BillingAddressPersister;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister;
use Magento\Quote\Model\ResourceModel\Quote;

/**
 * Handler for saving quote.
 */
class SaveHandler
{
    /**
     * @var CartItemPersister
     */
    private $cartItemPersister;

    /**
     * @var BillingAddressPersister
     */
    private $billingAddressPersister;

    /**
     * @var Quote
     */
    private $quoteResourceModel;

    /**
     * @var ShippingAssignmentPersister
     */
    private $shippingAssignmentPersister;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $quoteAddressFactory;

    /**
     * @param Quote $quoteResource
     * @param CartItemPersister $cartItemPersister
     * @param BillingAddressPersister $billingAddressPersister
     * @param ShippingAssignmentPersister $shippingAssignmentPersister
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        Quote $quoteResource,
        CartItemPersister $cartItemPersister,
        BillingAddressPersister $billingAddressPersister,
        ShippingAssignmentPersister $shippingAssignmentPersister,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory
    ) {
        $this->quoteResourceModel = $quoteResource;
        $this->cartItemPersister = $cartItemPersister;
        $this->billingAddressPersister = $billingAddressPersister;
        $this->shippingAssignmentPersister = $shippingAssignmentPersister;
        $this->addressRepository = $addressRepository;
        $this->quoteAddressFactory = $addressFactory;
    }

    /**
     * Process and save quote data
     *
     * @param CartInterface $quote
     * @return CartInterface
     * @throws InputException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function save(CartInterface $quote)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        // Quote Item processing
        $items = $quote->getItems();

        if ($items) {
            foreach ($items as $item) {
                /** @var Item $item */
                if (!$item->isDeleted()) {
                    $quote->setLastAddedItem($this->cartItemPersister->save($quote, $item));
                } elseif (count($items) === 1) {
                    $quote->setBillingAddress($this->quoteAddressFactory->create());
                    $quote->setShippingAddress($this->quoteAddressFactory->create());
                }
            }
        }

        // Billing Address processing
        $billingAddress = $quote->getBillingAddress();

        if ($billingAddress) {
            if ($billingAddress->getCustomerAddressId()) {
                try {
                    $this->addressRepository->getById($billingAddress->getCustomerAddressId());
                } catch (NoSuchEntityException $e) {
                    $billingAddress->setCustomerAddressId(null);
                }
            }

            $this->billingAddressPersister->save($quote, $billingAddress);
        }

        $this->processShippingAssignment($quote);
        $this->quoteResourceModel->save($quote->collectTotals());

        return $quote;
    }

    /**
     * Process shipping assignment
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     * @throws InputException
     */
    private function processShippingAssignment($quote)
    {
        // Shipping Assignments processing
        $extensionAttributes = $quote->getExtensionAttributes();

        if (!$quote->isVirtual() && $extensionAttributes && $extensionAttributes->getShippingAssignments()) {
            $shippingAssignments = $extensionAttributes->getShippingAssignments();

            if (count($shippingAssignments) > 1) {
                throw new InputException(__('Only 1 shipping assignment can be set'));
            }

            $this->shippingAssignmentPersister->save($quote, $shippingAssignments[0]);
        }
    }
}
