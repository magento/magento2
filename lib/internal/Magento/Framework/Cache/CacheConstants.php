<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

/**
 * Cache cleaning mode constants
 *
 * These constants define the available cache cleaning modes.
 * Replaces Zend_Cache constants to eliminate dependency on Zend Cache library.
 *
 * @api
 */
class CacheConstants
{
    /**
     * Cleaning mode: all (removes all cache entries)
     */
    public const CLEANING_MODE_ALL = 'all';

    /**
     * Cleaning mode: old (removes expired cache entries only)
     */
    public const CLEANING_MODE_OLD = 'old';

    /**
     * Cleaning mode: matching tag (removes entries matching ALL given tags - AND logic)
     */
    public const CLEANING_MODE_MATCHING_TAG = 'matchingTag';

    /**
     * Cleaning mode: not matching tag (removes entries NOT matching any given tags - Inverse logic)
     */
    public const CLEANING_MODE_NOT_MATCHING_TAG = 'notMatchingTag';

    /**
     * Cleaning mode: matching any tag (removes entries matching ANY given tag - OR logic)
     */
    public const CLEANING_MODE_MATCHING_ANY_TAG = 'matchingAnyTag';
}
