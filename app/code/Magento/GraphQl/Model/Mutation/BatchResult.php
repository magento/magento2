<?php
/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Mutation;

class BatchResult
{
    const STATUS_SUCCESS = 'SUCCESS';

    const STATUS_FAILURE = 'FAILURE';

    const STATUS_MIXED = 'MIXED_RESULTS';
}
