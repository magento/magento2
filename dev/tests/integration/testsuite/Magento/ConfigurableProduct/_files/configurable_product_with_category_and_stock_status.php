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

    Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category.php');
    Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable_stock_status.php');

    /** @var CategoryLinkManagementInterface $categoryLinkManagement */
    $categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
    /** @var DefaultCategory $categoryHelper */
    $categoryHelper = $objectManager->get(DefaultCategory::class);

    $s = ['simple_10', 'simple_20', 'configurable_'];
    $productSkus = [];
    for ($i = 1; $i <= 3; $i++) {
        $suffixed_array = array_map(static function ($s) use ($i): string {
            return $s . $i;
        }, $s);
        $productSkus[] = $suffixed_array;
    }
    $productSkus = array_merge([], ...$productSkus);

    foreach ($productSkus as $sku) {
        $categoryLinkManagement->assignProductToCategories($sku, [$categoryHelper->getId(), 333]);
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    // Nothing to remove
}
