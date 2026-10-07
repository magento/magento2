<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Exception;

/**
 * Session expired exception
 *
 * Thrown when a valid session was invalidated on purpose (e.g. the customer password was changed or reset
 * from another browser) and has already been destroyed. The request can be safely redirected.
 *
 * @api
 */
class SessionExpiredException extends SessionException
{
}
