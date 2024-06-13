<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModuleDefaultHydrator') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestModuleDefaultHydrator', __DIR__);
}
