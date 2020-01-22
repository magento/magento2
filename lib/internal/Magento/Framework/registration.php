<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::LIBRARY, 'magento/framework', __DIR__);

if (!function_exists('__')) {
    require 'Phrase/__.php';
}
