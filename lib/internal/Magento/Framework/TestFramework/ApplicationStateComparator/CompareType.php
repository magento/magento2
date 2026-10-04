<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\ApplicationStateComparator;

/**
 * What type of comparison
 */
class CompareType
{
    public const COMPARE_BETWEEN_REQUESTS = "CompareBetweenRequests";
    public const COMPARE_CONSTRUCTED_AGAINST_CURRENT = "CompareConstructedAgainstCurrent";
}
