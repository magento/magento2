<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModule2') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestModule2', __DIR__);
}
