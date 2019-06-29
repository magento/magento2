<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper;

/**
 * Helper for extracting information from the payment data structure
 */
class SubjectReader
{
    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject): PaymentDataObjectInterface
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads store's ID, otherwise returns null.
     *
     * @param array $subject
     * @return int|null
     */
    public function readStoreId(array $subject): ?int
    {
        $storeId = $subject['store_id'] ?? null;

        if (empty($storeId)) {
            try {
                $storeId = (int)$this->readPayment($subject)
                    ->getOrder()
                    ->getStoreId();
            } catch (\InvalidArgumentException $e) {
                // No store id is current set
            }
        }

        return $storeId ? (int)$storeId : null;
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return string
     */
    public function readAmount(array $subject): string
    {
        return (string)Helper\SubjectReader::readAmount($subject);
    }

    /**
     * Reads response from subject
     *
     * @param array $subject
     * @return array
     */
    public function readResponse(array $subject): ?array
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
