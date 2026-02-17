<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModuleGraphQlQueryExtension') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestModuleGraphQlQueryExtension', __DIR__);
}
