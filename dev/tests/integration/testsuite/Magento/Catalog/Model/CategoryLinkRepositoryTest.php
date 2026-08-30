<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory;
use Magento\Catalog\Test\Fixture\AssignProducts as AssignProductsFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Tests that a single product to category link is written without discarding the rest of the category product list.
 */
#[
    AppArea('adminhtml'),
    DbIsolation(true),
]
class CategoryLinkRepositoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CategoryLinkRepositoryInterface
     */
    private $categoryLinkRepository;

    /**
     * @var CategoryProductLinkInterfaceFactory
     */
    private $productLinkFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryLinkRepository = $this->objectManager->get(CategoryLinkRepositoryInterface::class);
        $this->productLinkFactory = $this->objectManager->get(CategoryProductLinkInterfaceFactory::class);
        $this->resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(CategoryRepository::class);
        parent::tearDown();
    }

    /**
     * Saving a single link must not drop the products already assigned to the category.
     *
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(ProductFixture::class, as: 'p3'),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(
            AssignProductsFixture::class,
            ['category' => '$category$', 'products' => ['$p1$', '$p2$']]
        ),
    ]
    public function testSaveKeepsExistingAssignments(): void
    {
        $category = $this->fixtures->get('category');
        $product = $this->fixtures->get('p3');

        $this->assertTrue($this->saveLink((int)$category->getId(), (string)$product->getSku(), 7));

        $positions = $this->getAssignedPositions((int)$category->getId());
        $this->assertCount(3, $positions);
        $this->assertArrayHasKey((int)$this->fixtures->get('p1')->getId(), $positions);
        $this->assertArrayHasKey((int)$this->fixtures->get('p2')->getId(), $positions);
        $this->assertSame(7, $positions[(int)$product->getId()]);
    }

    /**
     * Saving a link for an already assigned product must only update its position.
     *
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(
            AssignProductsFixture::class,
            ['category' => '$category$', 'products' => ['$p1$', '$p2$']]
        ),
    ]
    public function testSaveUpdatesPositionOfAssignedProduct(): void
    {
        $category = $this->fixtures->get('category');
        $product = $this->fixtures->get('p1');

        $this->assertTrue($this->saveLink((int)$category->getId(), (string)$product->getSku(), 11));

        $positions = $this->getAssignedPositions((int)$category->getId());
        $this->assertCount(2, $positions);
        $this->assertSame(11, $positions[(int)$product->getId()]);
    }

    /**
     * Removing a single link must not drop the other products assigned to the category.
     *
     * @return void
     */
    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(
            AssignProductsFixture::class,
            ['category' => '$category$', 'products' => ['$p1$', '$p2$']]
        ),
    ]
    public function testDeleteByIdsKeepsOtherAssignments(): void
    {
        $category = $this->fixtures->get('category');
        $product = $this->fixtures->get('p1');

        $this->assertTrue(
            $this->categoryLinkRepository->deleteByIds((int)$category->getId(), $product->getSku())
        );

        $this->assertSame(
            [(int)$this->fixtures->get('p2')->getId()],
            array_keys($this->getAssignedPositions((int)$category->getId()))
        );
    }

    /**
     * The category save pipeline must still run, so that the category based product url rewrite is generated.
     *
     * @return void
     */
    #[
        Config('catalog/seo/generate_category_product_rewrites', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CategoryFixture::class, as: 'category'),
    ]
    public function testSaveGeneratesCategoryProductUrlRewrite(): void
    {
        $category = $this->fixtures->get('category');
        $product = $this->fixtures->get('product');

        $this->saveLink((int)$category->getId(), (string)$product->getSku(), 1);

        /** @var UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);
        $rewrites = $urlFinder->findAllByData(
            [
                UrlRewrite::ENTITY_ID => $product->getId(),
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            ]
        );

        $categoryBasedRewrites = [];
        foreach ($rewrites as $rewrite) {
            if (str_contains((string)$rewrite->getRequestPath(), '/')) {
                $categoryBasedRewrites[] = $rewrite->getRequestPath();
            }
        }

        $this->assertNotEmpty(
            $categoryBasedRewrites,
            'A category based url rewrite is expected for the newly assigned product.'
        );
    }

    /**
     * Assign the product to the category through the repository under test.
     *
     * @param int $categoryId
     * @param string $sku
     * @param int $position
     * @return bool
     */
    private function saveLink(int $categoryId, string $sku, int $position): bool
    {
        $productLink = $this->productLinkFactory->create();
        $productLink->setCategoryId($categoryId);
        $productLink->setSku($sku);
        $productLink->setPosition($position);

        return $this->categoryLinkRepository->save($productLink);
    }

    /**
     * Read the product id to position map straight from the link table.
     *
     * @param int $categoryId
     * @return array
     */
    private function getAssignedPositions(int $categoryId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('catalog_category_product'), ['product_id', 'position'])
            ->where('category_id = ?', $categoryId);

        $positions = [];
        foreach ($connection->fetchAll($select) as $row) {
            $positions[(int)$row['product_id']] = (int)$row['position'];
        }

        return $positions;
    }
}
