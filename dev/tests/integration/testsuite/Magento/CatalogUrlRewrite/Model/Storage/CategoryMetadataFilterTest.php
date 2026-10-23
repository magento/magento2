<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Storage;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Filtering stored product-in-category URL rewrites by the category_id metadata in both catalog storages.
 */
class CategoryMetadataFilterTest extends TestCase
{
    /**
     * @param string $storageClass
     * @param string[] $categoryAliases
     * @param bool $asArray
     */
    #[
        DbIsolation(true),
        AppIsolation(true),
        Config('catalog/seo/generate_category_product_rewrites', 1),
        DataFixture(CategoryFixture::class, as: 'category1'),
        DataFixture(CategoryFixture::class, as: 'category2'),
        DataFixture(CategoryFixture::class, as: 'category3'),
        DataFixture(
            ProductFixture::class,
            ['category_ids' => ['$category1.id$', '$category2.id$', '$category3.id$']],
            as: 'product'
        ),
        DataProvider('categoryFilterDataProvider'),
    ]
    public function testFindAllByDataFiltersByCategoryMetadata(
        string $storageClass,
        array $categoryAliases,
        bool $asArray
    ): void {
        $fixtures = DataFixtureStorageManager::getStorage();
        $categoryIds = array_map(
            static fn (string $alias) => (int)$fixtures->get($alias)->getId(),
            $categoryAliases
        );
        $categoryFilter = $asArray ? $categoryIds : reset($categoryIds);

        $storage = Bootstrap::getObjectManager()->create($storageClass);
        $rewrites = $storage->findAllByData(
            [
                UrlRewrite::ENTITY_ID => [(int)$fixtures->get('product')->getId()],
                UrlRewrite::ENTITY_TYPE => 'product',
                UrlRewrite::STORE_ID => 1,
                UrlRewrite::REDIRECT_TYPE => [0],
                UrlRewrite::METADATA => ['category_id' => $categoryFilter],
            ]
        );

        $foundCategoryIds = array_map(
            static fn (UrlRewrite $rewrite) => (int)($rewrite->getMetadata()['category_id'] ?? 0),
            $rewrites
        );
        sort($foundCategoryIds);
        sort($categoryIds);
        $this->assertSame($categoryIds ?: [0], $foundCategoryIds);
    }

    /**
     * @return array
     */
    public static function categoryFilterDataProvider(): array
    {
        $cases = [];
        foreach ([DbStorage::class, DynamicStorage::class] as $storageClass) {
            $name = substr($storageClass, strrpos($storageClass, '\\') + 1);
            $cases[$name . ': single category'] = [$storageClass, ['category1'], false];
            $cases[$name . ': one category in an array'] = [$storageClass, ['category2'], true];
            $cases[$name . ': two categories'] = [$storageClass, ['category1', 'category3'], true];
            $cases[$name . ': no category'] = [$storageClass, [], true];
        }

        return $cases;
    }
}
