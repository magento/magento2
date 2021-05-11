<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteRepository;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea crontab
 * @magentoDbIsolation disabled
 */
class UpdateProductWebsiteUrlRewritesTest extends TestCase
{
    /**
     * @var Action
     */
    private $action;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->action = $objectManager->get(Action::class);
        $this->storeWebsiteRelation = $objectManager->get(StoreWebsiteRelationInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     */
    public function testUpdateUrlRewrites()
    {
        /** @var Website $website */
        $websiteRepository = Bootstrap::getObjectManager()->get(WebsiteRepository::class);
        $website = $websiteRepository->get('test');
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $product =  $productRepository->get('simple1', false, null, true);
        $this->action->updateWebsites([$product->getId()], [$website->getId()], 'add');
        $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($website->getId());

        $this->assertStringContainsString(
            $product->getUrlKey() . '.html',
            $product->setStoreId(reset($storeIds))->getProductUrl()
        );

        $this->action->updateWebsites([$product->getId()], [$website->getId()], 'remove');
        $product->setRequestPath('');
        $url = $product->setStoreId(reset($storeIds))->getProductUrl();
        $this->assertStringNotContainsString(
            $product->getUrlKey() . '.html',
            $url
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     */
    public function testUpdateUrlRewritesForSecondProduct()
    {
        /** @var Website $website */
        $websiteRepository = Bootstrap::getObjectManager()->get(WebsiteRepository::class);
        $website = $websiteRepository->get('test');
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        // test the first product
        $product =  $productRepository->get('simple1', false, null, true);
        // store filter condition about the first product in collection
        $productCollection = Bootstrap::getObjectManager()->get(ProductCollection::class);
        $productCollection->addFieldToFilter('entity_id', $product->getId());
        $this->action->updateWebsites([$product->getId()], [$website->getId()], 'add');
        $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($website->getId());
        $this->assertStringContainsString(
            $product->getUrlKey() . '.html',
            $product->setStoreId(reset($storeIds))->getProductUrl()
        );
        $this->action->updateWebsites([$product->getId()], [$website->getId()], 'remove');
        $product->setRequestPath('');
        $url = $product->setStoreId(reset($storeIds))->getProductUrl();
        $this->assertStringNotContainsString(
            $product->getUrlKey() . '.htmll',
            $url
        );
        // test the second product
        $product =  $productRepository->get('simple2', false, null, true);
        $this->action->updateWebsites([$product->getId()], [$website->getId()], 'add');
        $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($website->getId());
        $this->assertStringContainsString(
            $product->getUrlKey() . '.html',
            $product->setStoreId(reset($storeIds))->getProductUrl()
        );
        $this->action->updateWebsites([$product->getId()], [$website->getId()], 'remove');
        $product->setRequestPath('');
        $url = $product->setStoreId(reset($storeIds))->getProductUrl();
        $this->assertStringNotContainsString(
            $product->getUrlKey() . '.html',
            $url
        );
    }
}
