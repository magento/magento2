<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$setup = $objectManager->get(ModuleDataSetupInterface::class);
/** @var EavSetup $eavSetup */
$eavSetup = $objectManager->get(EavSetupFactory::class)
                          ->create(['setup' => $setup]);
$eavSetup->removeAttribute(Product::ENTITY, 'zzz');
