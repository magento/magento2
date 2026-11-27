<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite as UrlRewriteFixture;
use PHPUnit\Framework\TestCase;

#[AppIsolation(true)]
#[DbIsolation(false)]
#[Config('web/url/use_store', 1)]
#[Config('catalog/seo/category_url_suffix', '.html')]
#[DataFixture(CategoryFixture::class, as: 'category')]
#[DataFixture(StoreFixture::class, as: 'second_store')]
class CategoryStoreUrlTest extends TestCase
{
    #[DataFixture(CategoryFixture::class, as: 'category')]
    #[DataFixture(StoreFixture::class, as: 'second_store')]
    #[DataFixture(
        UrlRewriteFixture::class,
        [
            'entity_type' => 'category',
            'entity_id' => '$category.id$',
            'store_id' => '$second_store.id$',
            'request_path' => 'second-store-category.html',
            'target_path' => 'catalog/category/view/id/$category.id$',
        ]
    )]
    public function testRewriteUsesCategoryStore(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $stores = $objectManager->get(StoreManagerInterface::class);
        $stores->setCurrentStore(1);
        $fixtures = DataFixtureStorageManager::getStorage();
        $store = $fixtures->get('second_store');
        $category = $objectManager->create(CategoryRepositoryInterface::class)->get(
            $fixtures->get('category')->getId(),
            $store->getId()
        );
        $rewrite = $objectManager->get(UrlFinderInterface::class)->findOneByData([
            'entity_id' => $category->getId(),
            'entity_type' => 'category',
            'store_id' => $store->getId(),
            'redirect_type' => 0,
        ]);
        self::assertNotNull($rewrite);
        self::assertFalse($category->hasData('request_path'));
        $url = $category->getUrl();
        self::assertStringContainsString($store->getCode(), $url);
        self::assertSame($store->getUrl('', ['_direct' => $rewrite->getRequestPath()]), $url);
        self::assertSame(1, (int)$stores->getStore()->getId());
    }

    public function testCategoryIdUrlUsesCategoryStore(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(StoreManagerInterface::class)->setCurrentStore(1);
        $fixtures = DataFixtureStorageManager::getStorage();
        $store = $fixtures->get('second_store');
        $category = $objectManager->create(CategoryRepositoryInterface::class)->get(
            $fixtures->get('category')->getId(),
            $store->getId()
        );
        $url = $category->getCategoryIdUrl();
        self::assertStringContainsString($store->getCode(), $url);
        self::assertSame($store->getUrl('catalog/category/view', [
            's' => $category->getUrlKey(),
            'id' => $category->getId(),
        ]), $url);
    }

    public function testCurrentStoreUrlsRemainUnchanged(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(StoreManagerInterface::class)->setCurrentStore(1);
        $category = $objectManager->create(CategoryRepositoryInterface::class)->get(
            DataFixtureStorageManager::getStorage()->get('category')->getId(),
            1
        );
        $url = $category->getUrlInstance();
        self::assertSame($url->getDirectUrl($category->getUrlKey() . '.html'), $category->getUrl());
        self::assertSame($url->getUrl('catalog/category/view', [
            's' => $category->getUrlKey(),
            'id' => $category->getId(),
        ]), $category->getCategoryIdUrl());
        $category->setData('url', null)->setData('request_path', 'preloaded-category.html');
        self::assertSame($url->getDirectUrl('preloaded-category.html'), $category->getUrl());
        $category->setData('url', 'https://example.com/custom-category');
        self::assertSame('https://example.com/custom-category', $category->getUrl());
    }

    public function testPreloadedRequestPathUsesCategoryStore(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(StoreManagerInterface::class)->setCurrentStore(1);
        $fixtures = DataFixtureStorageManager::getStorage();
        $store = $fixtures->get('second_store');
        $category = $objectManager->create(CategoryRepositoryInterface::class)->get(
            $fixtures->get('category')->getId(),
            $store->getId()
        );
        $category->setData('url', null)->setData('request_path', 'preloaded-category.html');
        $url = $category->getUrl();
        self::assertStringContainsString($store->getCode(), $url);
        self::assertSame($store->getUrl('', ['_direct' => 'preloaded-category.html']), $url);
    }
}
