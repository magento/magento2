<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Layer\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for category collection provider.
 *
 * @see \Magento\Catalog\Model\Layer\Category\ItemCollectionProvider
 * @magentoAppArea frontend
 */
class ItemCollectionProviderTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var ItemCollectionProvider */
    private $itemCollectionProvider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->itemCollectionProvider = $this->objectManager->get(ItemCollectionProvider::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testGetCollection(): void
    {
        $category = $this->categoryRepository->get(333);
        $categoryProductsCollection = $this->itemCollectionProvider->getCollection($category);
        $this->assertCount(1, $categoryProductsCollection);
        $this->assertEquals('simple333', $categoryProductsCollection->getFirstItem()->getSku());
    }
}
