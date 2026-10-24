<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Encryption\Adapter;

/**
 * Encryption adapter interface
 *
 * @api
 */
interface EncryptionAdapterInterface
{
    /**
     * @param $data
     * @return string
     */
    public function encrypt(string $data): string;

    /**
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string;
}
