<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Payment;

/**
 * Resource for updating encrypted credit card data to the latest cipher
 */
class EncryptionUpdate
{
    const LEGACY_PATTERN = '^[[:digit:]]+:[^%s]:.*$';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment
     */
    private $paymentResource;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment $paymentResource
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Payment $paymentResource,
        \Magento\Framework\Encryption\Encryptor $encryptor
    ) {
        $this->paymentResource = $paymentResource;
        $this->encryptor = $encryptor;
    }

    /**
     * Fetch encrypted credit card numbers using legacy ciphers and re-encrypt with latest cipher
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reEncryptCreditCardNumbers()
    {
        $connection = $this->paymentResource->getConnection();
        $table = $this->paymentResource->getMainTable();
        $select = $connection->select()->from($table, ['entity_id', 'cc_number_enc'])
            ->where(
                'cc_number_enc REGEXP ?',
                sprintf(self::LEGACY_PATTERN, \Magento\Framework\Encryption\Encryptor::CIPHER_LATEST)
            )->limit(1000);

        while ($attributeValues = $connection->fetchPairs($select)) {
                // save new values
            foreach ($attributeValues as $valueId => $value) {
                $connection->update(
                    $table,
                    ['cc_number_enc' => $this->encryptor->encrypt($this->encryptor->decrypt($value))],
                    ['entity_id = ?' => (int)$valueId, 'cc_number_enc = ?' => (string)$value]
                );
            }
        }
    }
}
