<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
    'magento/framework',
    __DIR__
);

if (!function_exists('__')) {
    require 'Phrase/__.php';
}
