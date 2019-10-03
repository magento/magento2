<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;

/**
 * Resets quote shipping assignments when item is removed from multishipping quote.
 */
class ResetShippingAssigment
{
    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     */
    public function __construct(
        ShippingAssignmentProcessor $shippingAssignmentProcessor
    ) {
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
    }

    /**
     * Resets quote shipping assignments when item is removed from multishipping quote.
     *
     * @param Quote $subject
     * @param mixed $itemId
     *
     * @return array
     */
    public function beforeRemoveItem(Quote $subject, $itemId): array
    {
        if ($subject->getIsMultiShipping()) {
            $extensionAttributes = $subject->getExtensionAttributes();
            if ($extensionAttributes && $extensionAttributes->getShippingAssignments()) {
                $shippingAssignment = $this->shippingAssignmentProcessor->create($subject);
                $extensionAttributes->setShippingAssignments([$shippingAssignment]);
            }
        }

        return [$itemId];
    }
}
