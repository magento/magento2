<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Store;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\StoreFactory;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

#[AppArea('adminhtml')]
class ViewTest extends TestCase
{
    #[
        DbIsolation(true),
        DataFixture(
            CategoryFixture::class,
            ['name' => 'Visible', 'url_key' => 'visible-category-new-store', 'include_in_menu' => true],
            as: 'visible'
        ),
        DataFixture(
            CategoryFixture::class,
            ['name' => 'Hidden', 'url_key' => 'hidden-category-new-store', 'include_in_menu' => false],
            as: 'hidden'
        ),
    ]
    public function testNewStoreViewGetsRewritesForCategoriesExcludedFromMenu(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $store = $objectManager->get(StoreFactory::class)->create();
        $store->setData([
            'code' => 'new_store_view_rewrites',
            'name' => 'New store view rewrites',
            'website_id' => 1,
            'group_id' => 1,
            'is_active' => 1,
        ]);
        $objectManager->get(StoreResource::class)->save($store);
        $storeId = (int)$store->getId();

        $fixtures = DataFixtureStorageManager::getStorage();
        $urlFinder = $objectManager->get(UrlFinderInterface::class);
        $suffix = (string)$objectManager->get(ScopeConfigInterface::class)
            ->getValue(CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX);

        foreach (['visible', 'hidden'] as $alias) {
            $category = $fixtures->get($alias);
            $rewrite = $urlFinder->findOneByData([
                UrlRewrite::ENTITY_ID => (int)$category->getId(),
                UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::STORE_ID => $storeId,
            ]);

            $this->assertNotNull(
                $rewrite,
                sprintf('No URL rewrite for the "%s" category in the new store view', $alias)
            );
            $this->assertSame($category->getUrlKey() . $suffix, $rewrite->getRequestPath());
        }
    }
}
