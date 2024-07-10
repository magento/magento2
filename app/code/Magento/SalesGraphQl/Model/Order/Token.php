<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Order;

use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Encrypt or decrypt order token
 */
class Token
{
    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        private readonly EncryptorInterface $encryptor
    ) {
    }

    /**
     * Encrypt number, email and postcode to create a token
     *
     * @param string $number
     * @param string $email
     * @param string $postcode
     * @return string
     */
    public function encrypt(string $number, string $email, string $postcode): string
    {
        return $this->encryptor->encrypt(implode('|', [$number, $email, $postcode]));
    }

    /**
     * Retrieve number, email and postcode from token
     *
     * @param string $token
     * @return string[]
     */
    public function decrypt(string $token): array
    {
        return explode('|', $this->encryptor->decrypt($token));
    }
}
