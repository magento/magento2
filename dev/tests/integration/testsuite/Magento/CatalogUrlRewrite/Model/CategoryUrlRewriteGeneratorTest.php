<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryUrlRewriteGeneratorTest extends TestCase
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_products.php
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGenerateUrlRewritesWithoutSaveHistory()
    {
        /** @var Category $category */
        $category = $this->objectManager->create(Category::class);
        $category->load(3);
        $category->setData('save_rewrites_history', false);
        $category->setUrlKey('new-url');
        $category->save();

        $categoryFilter = [
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::ENTITY_ID => [3, 4, 5]
        ];
        $actualResults = $this->getActualResults($categoryFilter);
        $categoryExpectedResult = [
            ['new-url.html', 'catalog/category/view/id/3', 1, 0],
            ['new-url/category-1-1.html', 'catalog/category/view/id/4', 1, 0],
            ['new-url/category-1-1/category-1-1-1.html', 'catalog/category/view/id/5', 1, 0],
        ];

        $this->assertResults($categoryExpectedResult, $actualResults);

        /** @var ProductRepository $productRepository */
        $productRepository = $this->objectManager->create(ProductRepository::class);
        $product = $productRepository->get('12345');
        $productForTest = $product->getId();

        $productFilter = [
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::ENTITY_ID => [$productForTest]
        ];
        $actualResults = $this->getActualResults($productFilter);
        $productExpectedResult = [
            [
                'simple-product-two.html',
                'catalog/product/view/id/' . $productForTest,
                1,
                0
            ],
            [
                'new-url/category-1-1/category-1-1-1/simple-product-two.html',
                'catalog/product/view/id/' . $productForTest . '/category/5',
                1,
                0
            ],
            [
                'new-url/simple-product-two.html',
                'catalog/product/view/id/' . $productForTest . '/category/3',
                1,
                0
            ],
            [
                'new-url/category-1-1/simple-product-two.html',
                'catalog/product/view/id/' . $productForTest . '/category/4',
                1,
                0
            ],
            [
                '/simple-product-two.html',
                'catalog/product/view/id/' . $productForTest . '/category/2',
                1,
                0
            ]
        ];

        $this->assertResults($productExpectedResult, $actualResults);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_products.php
     * @magentoAppIsolation enabled
     */
    public function testGenerateUrlRewritesWithSaveHistory()
    {
        /** @var Category $category */
        $category = $this->objectManager->create(Category::class);
        $category->load(3);
        $category->setData('save_rewrites_history', true);
        $category->setUrlKey('new-url');
        $category->save();

        $categoryFilter = [
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::ENTITY_ID => [3, 4, 5]
        ];
        $actualResults = $this->getActualResults($categoryFilter);
        $categoryExpectedResult = [
            ['new-url.html', 'catalog/category/view/id/3', 1, 0],
            ['new-url/category-1-1.html', 'catalog/category/view/id/4', 1, 0],
            ['new-url/category-1-1/category-1-1-1.html', 'catalog/category/view/id/5', 1, 0],
            ['category-1.html', 'new-url.html', 0, OptionProvider::PERMANENT],
            ['category-1/category-1-1.html', 'new-url/category-1-1.html', 0, OptionProvider::PERMANENT],
            [
                'category-1/category-1-1/category-1-1-1.html',
                'new-url/category-1-1/category-1-1-1.html',
                0,
                OptionProvider::PERMANENT
            ],
        ];

        $this->assertResults($categoryExpectedResult, $actualResults);

        /** @var ProductRepository $productRepository */
        $productRepository = $this->objectManager->create(ProductRepository::class);
        $product = $productRepository->get('12345');
        $productForTest = $product->getId();

        $productFilter = [
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::ENTITY_ID => [$productForTest]
        ];
        $actualResults = $this->getActualResults($productFilter);
        $productExpectedResult = [
            [
                'simple-product-two.html',
                'catalog/product/view/id/' . $productForTest,
                1,
                0
            ],
            [
                'new-url/category-1-1/category-1-1-1/simple-product-two.html',
                'catalog/product/view/id/' . $productForTest . '/category/5',
                1,
                0
            ],
            [
                'category-1/category-1-1/category-1-1-1/simple-product-two.html',
                'new-url/category-1-1/category-1-1-1/simple-product-two.html',
                0,
                OptionProvider::PERMANENT
            ],
            [
                'new-url/simple-product-two.html',
                'catalog/product/view/id/' . $productForTest . '/category/3',
                1,
                0
            ],
            [
                'new-url/category-1-1/simple-product-two.html',
                'catalog/product/view/id/' . $productForTest . '/category/4',
                1,
                0
            ],
            [
                '/simple-product-two.html',
                'catalog/product/view/id/' . $productForTest . '/category/2',
                1,
                0
            ],
            [
                'category-1/simple-product-two.html',
                'new-url/simple-product-two.html',
                0,
                OptionProvider::PERMANENT
            ],
            [
                'category-1/category-1-1/simple-product-two.html',
                'new-url/category-1-1/simple-product-two.html',
                0,
                OptionProvider::PERMANENT
            ],
        ];

        $this->assertResults($productExpectedResult, $actualResults);
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @param string $urlKey
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws Exception
     * @dataProvider incorrectUrlRewritesDataProvider
     */
    public function testGenerateUrlRewritesWithIncorrectUrlKey($urlKey)
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid URL key');
        /** @var CategoryRepositoryInterface $repository */
        $repository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $category = $repository->get(3);
        $category->setUrlKey($urlKey);
        $repository->save($category);
    }

    /**
     * @return array
     */
    public function incorrectUrlRewritesDataProvider()
    {
        return [
            ['#'],
            ['//']
        ];
    }

    /**
     * @param array $filter
     * @return array
     */
    protected function getActualResults(array $filter)
    {
        /** @var UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $actualResults = [];
        foreach ($urlFinder->findAllByData($filter) as $url) {
            $actualResults[] = [
                $url->getRequestPath(),
                $url->getTargetPath(),
                (int)$url->getIsAutogenerated(),
                $url->getRedirectType()
            ];
        }
        return $actualResults;
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 0
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_products.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGenerateUrlRewritesWithoutGenerateCategoryRewrites()
    {
        /** @var Category $category */
        $category = $this->objectManager->create(Category::class);
        $category->load(3);
        $category->setData('save_rewrites_history', false);
        $category->setUrlKey('new-url');
        $category->save();

        $categoryFilter = [
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::ENTITY_ID => [3, 4, 5]
        ];
        $actualResults = $this->getActualResults($categoryFilter);
        $categoryExpectedResult = [
            ['new-url.html', 'catalog/category/view/id/3', 1, 0],
            ['new-url/category-1-1.html', 'catalog/category/view/id/4', 1, 0],
            ['new-url/category-1-1/category-1-1-1.html', 'catalog/category/view/id/5', 1, 0],
        ];

        $this->assertResults($categoryExpectedResult, $actualResults);
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 0
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_products.php
     * @magentoAppIsolation enabled
     */
    public function testGenerateUrlRewritesWithoutGenerateProductRewrites()
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->objectManager->create(ProductRepository::class);
        $product = $productRepository->get('12345');
        $productForTest = $product->getId();

        $productFilter = [
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::ENTITY_ID => [$productForTest]
        ];
        $actualResults = $this->getActualResults($productFilter);
        $productExpectedResult = [
            [
                'simple-product-two.html',
                'catalog/product/view/id/' . $productForTest,
                1,
                0
            ]
        ];

        $this->assertResults($productExpectedResult, $actualResults);
    }

    /**
     * Check number of records after removing product
     *
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_products.php
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testRemoveCatalogUrlRewrites()
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->objectManager->create(CategoryRepository::class);
        $category = $categoryRepository->get(5);
        $categoryId = $category->getId();

        /** @var ProductRepository $productRepository */
        $productRepository = $this->objectManager->create(ProductRepository::class);
        $product = $productRepository->get('12345');
        $productId = $product->getId();

        $countBeforeRemoving = $this->getCountOfRewrites($productId, $categoryId);
        $productRepository->delete($product);
        $countAfterRemoving = $this->getCountOfRewrites($productId, $categoryId);
        $this->assertEquals($countBeforeRemoving - 1, $countAfterRemoving);
    }

    /**
     * Get count of records in table
     *
     * @param $productId
     * @param $categoryId
     * @return string
     */
    private function getCountOfRewrites($productId, $categoryId): string
    {
        /** @var Product $model */
        $model = $this->objectManager->get(Product::class);
        $connection = $model->getConnection();
        $select = $connection->select();
        $select->from(Product::TABLE_NAME, 'COUNT(*)');
        $select->where('category_id = ?', $categoryId);
        $select->where('product_id = ?', $productId);
        return $connection->fetchOne($select);
    }

    /**
     * @param array $expected
     * @param array $actual
     * @throws Exception
     */
    protected function assertResults($expected, $actual)
    {
        $this->assertEquals(count($expected), count($actual), 'Number of rewrites does not match');
        foreach ($expected as $row) {
            $this->assertContains(
                $row,
                $actual,
                'Expected: ' . var_export($row, true) . "\nIn Actual: " . var_export($actual, true)
            );
        }
    }
}
