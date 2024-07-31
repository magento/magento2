<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
