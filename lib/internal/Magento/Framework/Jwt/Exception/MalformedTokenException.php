<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Exception;

/**
 * Thrown when a token is either not a JWT, it is impossible to read with given settings or it's a bad JWT.
 */
class MalformedTokenException extends JwtException
{

}
