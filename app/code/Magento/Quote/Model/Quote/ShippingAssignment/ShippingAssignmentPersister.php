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
 * @since 2.1.0
 */
class ShippingAssignmentPersister
{
    /**
     * @var ShippingAssignmentProcessor
     * @since 2.1.0
     */
    private $shippingAssignmentProcessor;

    /**
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @since 2.1.0
     */
    public function __construct(ShippingAssignmentProcessor $shippingAssignmentProcessor)
    {
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
    }

    /**
     * @param CartInterface $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return void
     * @since 2.1.0
     */
    public function save(CartInterface $quote, ShippingAssignmentInterface $shippingAssignment)
    {
        if ($quote->getIsActive()) {
            $this->shippingAssignmentProcessor->save($quote, $shippingAssignment);
        }
    }
}
