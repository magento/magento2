<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use InvalidArgumentException;

/**
 * Store switch redirect data is no longer available, e.g. it was already consumed by an earlier request
 */
class RedirectDataNotFoundException extends InvalidArgumentException
{
}
