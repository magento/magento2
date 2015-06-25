<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Helper;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class SubjectReader
{
    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public static function readPayment(array $subject)
    {
        if (!isset($subject['payment'])
            || !$subject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException();
        }

        return $subject['payment'];
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return int|double|string
     */
    public static function readAmount(array $subject)
    {
        if ($subject['amount'] && !is_numeric($subject['amount'])) {
            throw new \InvalidArgumentException();
        }

        return $subject['amount'];
    }
}
