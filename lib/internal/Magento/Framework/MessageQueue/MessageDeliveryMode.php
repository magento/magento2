<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

enum MessageDeliveryMode: int
{
    case NON_PERSISTENT = 1;
    case PERSISTENT = 2;
}
