<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CategoryLinkTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryLink
     */
    private $categoryLink;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
        $this->categoryLink = $objectManager->get(CategoryLink::class);
    }

    protected function tearDown(): void
    {
        $this->categoryLink->resetCategoryLinksCache();
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CategoryFixture::class, as: 'category')
    ]
    public function testCategorySideProductAssignmentInvalidatesCachedCategoryLinks(): void
    {
        $product = $this->fixtures->get('product');
        $category = $this->fixtures->get('category');

        $this->assertSame([], $this->categoryLink->getCategoryLinks($product));

        $category->setPostedProducts([(int)$product->getId() => 0]);
        $this->categoryRepository->save($category);

        $categoryIds = array_column($this->categoryLink->getCategoryLinks($product), 'category_id');

        $this->assertContains((int)$category->getId(), array_map('intval', $categoryIds));
    }
}
