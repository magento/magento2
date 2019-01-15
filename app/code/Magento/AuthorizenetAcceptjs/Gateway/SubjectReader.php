<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AuthorizenetAcceptjs\Gateway;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper;

/**
 * Class SubjectReader
 */
class SubjectReader
{
    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads store's ID, otherwise returns null.
     *
     * @param array $subject
     * @return int|null
     */
    public function readStoreId(array $subject)
    {
        $storeId = $subject['store_id'] ?? null;

        if (empty($storeId)) {
            try {
                $storeId = $this->readPayment($subject)->getOrder()->getStoreId();
            } catch (\InvalidArgumentException $e) {}
        }

        return $storeId;
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * Reads response from subject
     *
     * @param array $subject
     * @return array
     */
    public function readResponse(array $subject)
    {
        return Helper\SubjectReader::readResponse($subject);
    }

    /**
     * Reads login id from subject
     *
     * @param array $subject
     * @return string|null
     */
    public function readLoginId(array $subject): ?string
    {
        return $subject['merchantAuthentication']['name'] ?? null;
    }

    /**
     * Reads transaction key from subject
     *
     * @param array $subject
     * @return string|null
     */
    public function readTransactionKey(array $subject): ?string
    {
        return $subject['merchantAuthentication']['transactionKey'] ?? null;
    }
}
