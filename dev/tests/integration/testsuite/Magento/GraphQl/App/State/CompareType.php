<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

/**
 * What type of comparison
 *
 * TODO: change this back into enum once magento-semvar is fixed
 */
class CompareType
{
    public const CompareBetweenRequests = "CompareBetweenRequests";
    public const CompareConstructedAgainstCurrent = "CompareConstructedAgainstCurrent";
}
