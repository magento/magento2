<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\ShippingAssignment;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Model\ShippingAssignmentFactory;

class ShippingAssignmentProcessor
{
    /**
     * @var ShippingAssignmentFactory
     */
    private $shippingAssignmentFactory;

    /**
     * @var ShippingProcessor
     */
    protected $shippingProcessor;

    /**
     * @var CartItemPersister
     */
    protected $cartItemPersister;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param ShippingAssignmentFactory $shippingAssignmentFactory
     * @param ShippingProcessor $shippingProcessor
     * @param CartItemPersister $cartItemPersister
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        ShippingAssignmentFactory $shippingAssignmentFactory,
        ShippingProcessor $shippingProcessor,
        CartItemPersister $cartItemPersister,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->shippingProcessor = $shippingProcessor;
        $this->cartItemPersister = $cartItemPersister;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Create shipping assignment
     *
     * @param CartInterface $quote
     * @return ShippingAssignmentInterface
     */
    public function create(CartInterface $quote)
    {
        /** @var Quote $quote */
        $shippingAddress = $quote->getShippingAddress();

        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $this->shippingAssignmentFactory->create();
        $shippingAssignment->setItems($quote->getItems());
        $shippingAssignment->setShipping($this->shippingProcessor->create($shippingAddress));

        return $shippingAssignment;
    }

    /**
     * Save shipping assignment
     *
     * @param CartInterface $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return void
     * @throws InputException|LocalizedException
     */
    public function save(CartInterface $quote, ShippingAssignmentInterface $shippingAssignment)
    {
        /** @var Quote $quote */
        foreach ($shippingAssignment->getItems() as $item) {
            /** @var Item $item */
            if (!$quote->getItemById($item->getItemId()) && !$item->isDeleted()) {
                $this->cartItemPersister->save($quote, $item);
            }
        }

        $shippingAddress = $shippingAssignment->getShipping()->getAddress();

        if ($shippingAddress->getCustomerAddressId()) {
            try {
                $this->addressRepository->getById($shippingAddress->getCustomerAddressId());
            } catch (NoSuchEntityException $e) {
                $shippingAddress->setCustomerAddressId(null);
            }
        }

        $this->shippingProcessor->save($shippingAssignment->getShipping(), $quote);
    }
}
