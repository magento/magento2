<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

try {
    Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category.php');
    Resolver::getInstance()->requireDataFixture(
        'Magento/Catalog/_files/multiple_mixed_products.php'
    );

    $objectManager = Bootstrap::getObjectManager();

    /** @var Registry $registry */
    $registry = Bootstrap::getObjectManager()->get(Registry::class);
    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', true);

    /** @var Config $configResource */
    $configResource = $objectManager->get(Config::class);
    $configResource->saveConfig(
        Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
        1,
        ScopeInterface::SCOPE_DEFAULT,
        0
    );

    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', false);

    /** @var CategoryLinkManagementInterface $categoryLinkManagement */
    $categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
    /** @var DefaultCategory $categoryHelper */
    $categoryHelper = $objectManager->get(DefaultCategory::class);

    $productSkus = [
        'simple_31',
        'simple_32',
        'configurable',
        'simple_41',
        'simple_42',
        'configurable_12345',
        'simple1',
        'simple2',
        'simple3'
    ];
    foreach ($productSkus as $sku) {
        $categoryLinkManagement->assignProductToCategories($sku, [$categoryHelper->getId(), 333]);
    }
} catch (\Exception $e) {
    // Nothing to remove
}
