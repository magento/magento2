<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\QuoteRepository;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\AddressInterfaceFactory;

/**
 * Handler for saving quote.
 */
class SaveHandler
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\CartItemPersister
     */
    private $cartItemPersister;

    /**
     * @var \Magento\Quote\Model\Quote\Address\BillingAddressPersister
     */
    private $billingAddressPersister;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $quoteResourceModel;

    /**
     * @var \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister
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
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     * @param \Magento\Quote\Model\Quote\Item\CartItemPersister $cartItemPersister
     * @param \Magento\Quote\Model\Quote\Address\BillingAddressPersister $billingAddressPersister
     * @param \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister $shippingAssignmentPersister
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory|null $addressFactory
     */
    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote $quoteResource,
        \Magento\Quote\Model\Quote\Item\CartItemPersister $cartItemPersister,
        \Magento\Quote\Model\Quote\Address\BillingAddressPersister $billingAddressPersister,
        \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister $shippingAssignmentPersister,
        AddressRepositoryInterface $addressRepository = null,
        AddressInterfaceFactory $addressFactory = null
    ) {
        $this->quoteResourceModel = $quoteResource;
        $this->cartItemPersister = $cartItemPersister;
        $this->billingAddressPersister = $billingAddressPersister;
        $this->shippingAssignmentPersister = $shippingAssignmentPersister;
        $this->addressRepository = $addressRepository
            ?: ObjectManager::getInstance()->get(AddressRepositoryInterface::class);
        $this->quoteAddressFactory = $addressFactory ?:ObjectManager::getInstance()
            ->get(AddressInterfaceFactory::class);
    }

    /**
     * Process and save quote data
     *
     * @param CartInterface $quote
     * @return CartInterface
     * @throws InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(CartInterface $quote)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        // Quote Item processing
        $items = $quote->getItems();

        if ($items) {
            foreach ($items as $item) {
                /** @var \Magento\Quote\Model\Quote\Item $item */
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
