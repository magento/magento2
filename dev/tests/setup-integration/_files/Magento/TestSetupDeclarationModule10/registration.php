<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestSetupDeclarationModule10') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestSetupDeclarationModule10', __DIR__);
}
