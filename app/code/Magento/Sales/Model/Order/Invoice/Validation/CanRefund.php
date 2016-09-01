<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice\Validation;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class CanRefund
 */
class CanRefund implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate($entity)
    {
        $messages = [];
        if (
            $entity->getState() != Invoice::STATE_PAID ||
            abs($entity->getBaseGrandTotal() - $entity->getBaseTotalRefunded()) < .0001
        ) {
            $messages[] = __('We can\'t create creditmemo for the invoice.');
        }

        return $messages;
    }
}
