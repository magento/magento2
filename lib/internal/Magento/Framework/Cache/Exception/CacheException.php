<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Cache exception - Symfony-compatible
 *
 * with a modern exception class compatible with Symfony Cache and PSR standards.
 *
 * @api
 */
class CacheException extends LocalizedException
{
}
