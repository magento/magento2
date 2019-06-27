<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Jose\Component\Checker\ClaimChecker;

/**
 * The proxy interface to support the library compatibility.
 * An implementation should have `checkClaim` and `supportedClaim` methods.
 * @see \Jose\Component\Checker\ClaimChecker implementation
 */
interface ClaimCheckerInterface extends ClaimChecker
{

}
