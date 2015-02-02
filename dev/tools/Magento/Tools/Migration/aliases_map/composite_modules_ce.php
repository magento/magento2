<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * List on composite module names for Magento CE
 */
require_once __DIR__ . '/../../../../../../app/bootstrap.php';
require_once realpath(
    dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))
) . '/app/code/Magento/Core/Model/Resource/SetupInterface.php';
require_once realpath(
    dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))
) . '/app/code/Magento/Core/Model/Resource/Setup.php';
require_once realpath(
    dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))
) . '/app/code/Magento/Core/Model/Resource/Setup/Migration.php';

$objectManager = new \Magento\Framework\App\ObjectManager();
return $objectManager->create('Magento\Framework\Module\Setup\Migration')->getCompositeModules();
