<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\ShippingAssignment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

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
        AddressRepositoryInterface $addressRepository = null
    ) {
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->shippingProcessor = $shippingProcessor;
        $this->cartItemPersister = $cartItemPersister;
        $this->addressRepository = $addressRepository
            ?: ObjectManager::getInstance()->get(AddressRepositoryInterface::class);
    }

    /**
     * Create shipping assignment
     *
     * @param CartInterface $quote
     * @return \Magento\Quote\Api\Data\ShippingAssignmentInterface
     */
    public function create(CartInterface $quote)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $shippingAddress = $quote->getShippingAddress();

        /** @var \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $this->shippingAssignmentFactory->create();
        $shippingAssignment->setItems($quote->getItems());
        $shippingAssignment->setShipping($this->shippingProcessor->create($shippingAddress));

        return $shippingAssignment;
    }

    /**
     * Save shipping assignment
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param CartInterface $quote
     * @return void
     * @throws InputException|LocalizedException
     */
    public function save(CartInterface $quote, ShippingAssignmentInterface $shippingAssignment)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        foreach ($shippingAssignment->getItems() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if (!$quote->getItemById($item->getItemId()) && !$item->isDeleted()) {
                $this->cartItemPersister->save($quote, $item);
            }
        }

        $shippingAddress = $quote->getShippingAddress();

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
