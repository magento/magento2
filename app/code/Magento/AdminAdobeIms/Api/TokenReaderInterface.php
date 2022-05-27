<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Api;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\InvalidArgumentException;

/**
 * Reads token data.
 */
interface TokenReaderInterface
{
    /**
     * Read data from a token.
     *
     * @param string $token
     * @return array
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws InvalidArgumentException
     */
    public function read(string $token);
}
