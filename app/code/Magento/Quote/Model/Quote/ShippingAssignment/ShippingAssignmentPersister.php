<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\ShippingAssignment;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

/**
 * Class \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister
 *
 */
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
