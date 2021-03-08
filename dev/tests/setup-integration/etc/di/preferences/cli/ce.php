<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework as MF;
use Magento\TestFramework as TF;

return [
    MF\App\AreaList::class => TF\App\AreaList::class,
    MF\Mview\TriggerCleaner::class => TF\Mview\DummyTriggerCleaner::class,
];
