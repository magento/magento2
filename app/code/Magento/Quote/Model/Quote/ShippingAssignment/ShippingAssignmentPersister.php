<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model\Quote\ShippingAssignment;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

class ShippingAssignmentPersister
{
    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     */
    public function __construct(ShippingAssignmentProcessor $shippingAssignmentProcessor)
    {
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
    }

    /**
     * @param CartInterface $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return void
     */
    public function save(CartInterface $quote, ShippingAssignmentInterface $shippingAssignment)
    {
        if ($quote->getIsActive()) {
            $this->shippingAssignmentProcessor->save($quote, $shippingAssignment);
        }
    }
}
