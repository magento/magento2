<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Reports\Observer\CatalogProductViewObserver;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_two_websites.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_for_second_website.php');

$objectManager = Bootstrap::getObjectManager();
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$websiteId = $storeManager->getStore('fixture_second_store')->getWebsiteId();
$customer = $customerRepository->get('customer@example.com', $websiteId);
/** @var MutableScopeConfigInterface $config */
$config = $objectManager->get(MutableScopeConfigInterface::class);
$originalValue = $config->getValue('reports/options/enabled');
$storeManager->setCurrentStore($storeManager->getStore('fixture_second_store')->getId());

try {
    /** @var CategoryProductIndexer $indexer */
    $indexer = $objectManager->create(CategoryProductIndexer::class);
    $indexer->executeFull();
    $config->setValue('reports/options/enabled', 1);
    /** @var Session $session */
    $session = $objectManager->get(Session::class);
    $session->loginById($customer->getId());
    $session->setCustomerId($customer->getId());
    /** @var ProductRepositoryInterface $productRepository */
    $productRepository = $objectManager->get(ProductRepositoryInterface::class);
    $product = $productRepository->get('simple-on-two-websites');
    $event = new DataObject(['product' => $product]);
    /** @var CatalogProductViewObserver $reportObserver */
    $reportObserver = $objectManager->get(CatalogProductViewObserver::class);
    $reportObserver->execute(new Observer(['event' => $event]));
} finally {
    $session->logout();
    $config->setValue('reports/options/enabled', $originalValue);
}
