<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if (null === $registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModuleTranslationPackage')) {
    ComponentRegistrar::register(
        ComponentRegistrar::MODULE,
        'Magento_TestModuleTranslationPackage',
        __DIR__
    );
}
