<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

/**
 * interface to check operation request is authorized or not
 */
interface OperationRequestAuthorizedInterface
{

    /**
     * Get operation request authorized
     *
     * @return bool
     */
    public function isRequestAuthorized(): bool;

    /**
     * Set operation request authorized
     *
     * @param bool $isOperationRequestAuthorized
     * @return bool
     */
    public function setRequestAuthorized(bool $isOperationRequestAuthorized): bool;
}
