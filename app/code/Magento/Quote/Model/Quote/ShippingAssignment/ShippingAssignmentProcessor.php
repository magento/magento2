<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\ShippingAssignment;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\Quote\Item\CartItemPersister;

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
     * @param ShippingAssignmentFactory $shippingAssignmentFactory
     * @param ShippingProcessor $shippingProcessor
     * @param CartItemPersister $cartItemPersister
     */
    public function __construct(
        ShippingAssignmentFactory $shippingAssignmentFactory,
        ShippingProcessor $shippingProcessor,
        CartItemPersister $cartItemPersister
    ) {
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->shippingProcessor = $shippingProcessor;
        $this->cartItemPersister = $cartItemPersister;
    }

    /**
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
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param CartInterface $quote
     * @return void
     * @throws InputException
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
        $this->shippingProcessor->save($shippingAssignment->getShipping(), $quote);
    }
}
