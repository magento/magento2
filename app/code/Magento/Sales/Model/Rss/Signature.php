<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Rss;

use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class for generating signature.
 */
class Signature
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * Sign data.
     *
     * @param string $data
     * @return string
     */
    public function signData($data)
    {
        return $this->encryptor->hash($data);
    }

    /**
     * Check if valid signature is provided for given data.
     *
     * @param string $data
     * @param string $signature
     * @return bool
     */
    public function isValid($data, $signature)
    {
        return $this->encryptor->validateHash($data, $signature);
    }
}
