<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor\Handler;

use Magento\Framework\ObjectManagerInterface;

/**
 * Re-encryptor handler error factory.
 */
class ErrorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates an error object.
     *
     * @param string $rowIdField
     * @param int|string $rowIdValue
     * @param string $message
     *
     * @return Error
     */
    public function create(string $rowIdField, int|string $rowIdValue, string $message): Error
    {
        return $this->objectManager->create(
            Error::class,
            [
                'rowIdField' => $rowIdField,
                'rowIdValue' => $rowIdValue,
                'message' => $message
            ]
        );
    }
}
