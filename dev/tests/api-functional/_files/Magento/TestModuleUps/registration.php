<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModuleUps') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestModuleUps', __DIR__);
}
