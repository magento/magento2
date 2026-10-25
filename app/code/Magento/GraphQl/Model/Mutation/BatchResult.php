<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Mutation;

class BatchResult
{
    public const STATUS_SUCCESS = 'SUCCESS';

    public const STATUS_FAILURE = 'FAILURE';

    public const STATUS_MIXED = 'MIXED_RESULTS';
}
