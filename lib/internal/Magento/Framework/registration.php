<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

<<<<<<< HEAD
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
    'magento/framework',
    __DIR__
);
=======
use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::LIBRARY, 'magento/framework', __DIR__);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

if (!function_exists('__')) {
    require 'Phrase/__.php';
}
