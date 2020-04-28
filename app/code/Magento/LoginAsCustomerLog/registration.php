<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Component\ComponentRegistrar;

declare(strict_types=1);

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Magento_LoginAsCustomerLog',
    __DIR__
);
