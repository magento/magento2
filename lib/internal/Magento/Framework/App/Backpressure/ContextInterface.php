<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure;

use Magento\Framework\App\RequestInterface;

/**
 * Request context
 */
interface ContextInterface
{
    public const IDENTITY_TYPE_IP = 0;

    public const IDENTITY_TYPE_CUSTOMER = 1;

    public const IDENTITY_TYPE_ADMIN = 2;

    /**
     * Current request
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;

    /**
     * Unique ID for request issuer
     *
     * @return string
     */
    public function getIdentity(): string;

    /**
     * Type of identity detected
     *
     * @return int
     */
    public function getIdentityType(): int;

    /**
     * Request type ID
     *
     * String ID of the functionality that requires backpressure enforcement
     *
     * @return string
     */
    public function getTypeId(): string;
}
