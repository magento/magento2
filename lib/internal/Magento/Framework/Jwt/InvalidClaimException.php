<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Jose\Component\Checker\InvalidClaimException as CoreInvalidClaimException;

/**
 * The wrapper for base InvalidClaimException implementation.
 * @see \Jose\Component\Checker\InvalidClaimException
 */
class InvalidClaimException extends CoreInvalidClaimException
{

}
