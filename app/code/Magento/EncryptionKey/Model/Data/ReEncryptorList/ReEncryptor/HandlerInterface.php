<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor;

use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor\Handler\Error;

/**
 * Interface for re-encryption handlers.
 */
interface HandlerInterface
{
    /**
     * Performs re-encryption.
     *
     * @return Error[] Non-critical DB row level errors (if any) that occurred during the process.
     *
     * @throws \Throwable In case of a critical error that prevents the process from completion.
     */
    public function reEncrypt(): array;
}
