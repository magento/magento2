<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Model\Data;

use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor;

/**
 * List of re-encryptors.
 *
 * Can be extended via DI configuration.
 */
class ReEncryptorList
{
    /**
     * @var ReEncryptor[]
     */
    private array $reEncryptors;

    /**
     * @param ReEncryptor[] $reEncryptors
     */
    public function __construct(array $reEncryptors = [])
    {
        $this->reEncryptors = $reEncryptors;
    }

    /**
     * Returns all available re-encryptors.
     *
     * @return ReEncryptor[]
     */
    public function getReEncryptors(): array
    {
        return $this->reEncryptors;
    }
}
