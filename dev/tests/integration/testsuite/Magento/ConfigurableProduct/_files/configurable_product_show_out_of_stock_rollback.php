<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogInventory\Model\Configuration;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

try {
    Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_rollback.php');
    Resolver::getInstance()->requireDataFixture(
        'Magento/Catalog/_files/multiple_mixed_products_rollback.php'
    );

    /** @var Registry $registry */
    $registry = Bootstrap::getObjectManager()->get(Registry::class);
    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', true);

    /** @var Config $configResource */
    $configResource = Bootstrap::getObjectManager()->create(Config::class);
    $configResource->deleteConfig(
        Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
        ScopeInterface::SCOPE_DEFAULT,
        0
    );
    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', false);
} catch (\Exception $e) {
    // Nothing to remove
}
