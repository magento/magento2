<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModuleGraphQlQuery') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestModuleGraphQlQuery', __DIR__);
}
