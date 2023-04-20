<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

class OperationRequestAuthorized implements OperationRequestAuthorizedInterface
{

    /**
     * @var bool
     */
    private $isOperationRequestAuthorized = false;

    /**
     * Get operation request authorized
     *
     * @return bool
     */
    public function isRequestAuthorized(): bool
    {
        return $this->isOperationRequestAuthorized;
    }

    /**
     * Set operation request authorized
     *
     * @param bool $isOperationRequestAuthorized
     * @return bool
     */
    public function setRequestAuthorized(bool $isOperationRequestAuthorized): bool
    {
        return $this->isOperationRequestAuthorized = $isOperationRequestAuthorized;
    }
}
