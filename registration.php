<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModuleMessageQueueConfigOverride') === null) {
    ComponentRegistrar::register(
        ComponentRegistrar::MODULE,
        'Magento_TestModuleMessageQueueConfigOverride',
        __DIR__
    );
}
