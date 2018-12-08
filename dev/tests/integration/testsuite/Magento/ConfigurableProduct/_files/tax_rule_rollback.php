<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('_fixture/Magento_Tax_Model_Calculation_Rule');
$objectManager->create(\Magento\Tax\Model\Calculation\Rule::class)->load('Test Rule', 'code')->delete();

$registry->unregister('_fixture/Magento_Tax_Model_Calculation_Rate');
$objectManager->create(\Magento\Tax\Model\Calculation\Rate::class)->loadByCode('*')->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
