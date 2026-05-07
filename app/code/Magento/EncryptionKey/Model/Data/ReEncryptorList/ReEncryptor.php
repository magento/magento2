<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Model\Data\ReEncryptorList;

use Throwable;
use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor\HandlerInterface;
use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor\Handler\Error;

/**
 * Generic re-encryptor.
 *
 * An item of \Magento\EncryptionKey\Model\Data\ReEncryptorList. Should be used
 * as a base for virtual classes of concrete re-encryptors.
 */
class ReEncryptor
{
    /**
     * @var string
     */
    private string $description;

    /**
     * @var HandlerInterface
     */
    private HandlerInterface $handler;

    /**
     * @param string $description
     * @param HandlerInterface $handler
     */
    public function __construct(
        string $description,
        HandlerInterface $handler
    ) {
        $this->description = $description;
        $this->handler = $handler;
    }

    /**
     * Returns description of the re-encryptor.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Performs re-encryption using provided handler.
     *
     * @return Error[] Non-critical DB row level errors (if any) that occurred during the process.
     *
     * @throws Throwable In case of a critical error that prevents the process from completion.
     */
    public function reEncrypt(): array
    {
        return $this->handler->reEncrypt();
    }
}
