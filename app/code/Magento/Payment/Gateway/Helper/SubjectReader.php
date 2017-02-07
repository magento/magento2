<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Helper;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Framework\DataObject;

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
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        return $subject['payment'];
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return mixed
     */
    public static function readAmount(array $subject)
    {
        if (!isset($subject['amount']) || !is_numeric($subject['amount'])) {
            throw new \InvalidArgumentException('Amount should be provided');
        }

        return $subject['amount'];
    }

    /**
     * Reads field from subject
     *
     * @param array $subject
     * @return string
     */
    public static function readField(array $subject)
    {
        if (!isset($subject['field']) || !is_string($subject['field'])) {
            throw new \InvalidArgumentException('Field does not exist');
        }

        return $subject['field'];
    }

    /**
     * Reads response NVP from subject
     *
     * @param array $subject
     * @return array
     */
    public static function readResponse(array $subject)
    {
        if (!isset($subject['response']) || !is_array($subject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        return $subject['response'];
    }

    /**
     * Read state object from subject
     *
     * @param array $subject
     * @return DataObject
     */
    public static function readStateObject(array $subject)
    {
        if (!isset($subject['stateObject']) || !$subject['stateObject'] instanceof DataObject) {
            throw new \InvalidArgumentException('State object does not exist');
        }

        return $subject['stateObject'];
    }
}
