<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

// TODO: change this back into enum once magento-semvar is fixed
class ShouldResetState
{
    public const DoResetState = "DoResetState";
    public const DoNotResetState = "DoNotResetState";
}
