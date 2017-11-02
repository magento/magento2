<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Indexer\Category\Product\Action\Full;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test class for \Magento\Catalog\Model\Category.
 * - tree knowledge is tested
 *
 * @see \Magento\Catalog\Model\CategoryTest
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CategoryTreeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var Category
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
     */
    private $_indexer;

    /**
     * @var CategoryRepositoryInterface $categoryRepository
     */
    private $_categoryRepository;

    /**
     * @var ResourceConnection
     */
    private $_resource;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create(Category::class);
        $this->_indexer = $this->_objectManager->create(Full::class);
        $this->_categoryRepository = $this->_objectManager->create(CategoryRepositoryInterface::class);
        $this->_resource = $this->_objectManager->create(ResourceConnection::class);
    }

    /**
     * Load category
     *
     * @param $categoryId
     * @return Category
     */
    protected function loadCategory($categoryId)
    {
        $this->_model->setData([]);
        $this->_model->load($categoryId);
        return $this->_model;
    }

    public function testMovePosition()
    {
        //move category 9 to new parent 6 with afterCategoryId = null
        $category = $this->loadCategory(9);
        $category->move(6, null);
        $category = $this->loadCategory(9);
        $this->assertEquals(1, $category->getPosition(), 'Position must be 1, if $afterCategoryId was null|false|0');
        $category = $this->loadCategory(10);
        $this->assertEquals(5, $category->getPosition(), 'Category 10 position must decrease after Category 9 moved');
        $category = $this->loadCategory(11);
        $this->assertEquals(6, $category->getPosition(), 'Category 11 position must decrease after Category 9 moved');
        $category = $this->loadCategory(6);
        $this->assertEquals(2, $category->getPosition(), 'Category 6 position must be the same');

        //move category 11 to new parent 6 with afterCategoryId = 9
        $category = $this->loadCategory(11);
        $category->move(6, 9);
        $category = $this->loadCategory(11);
        $this->assertEquals(2, $category->getPosition(), 'Category 11 position must be after category 9');
        $category = $this->loadCategory(10);
        $this->assertEquals(5, $category->getPosition(), 'Category 10 position must be the same');
        $category = $this->loadCategory(9);
        $this->assertEquals(1, $category->getPosition(), 'Category 9 position must be 1');
    }

    public function testMove()
    {
        $this->_model->load(7);
        $this->assertEquals(2, $this->_model->getParentId());
        $this->_model->move(6, 0);
        /* load is not enough to reset category data */
        $this->_model->setData([]);
        $this->_model->load(7);
        $this->assertEquals(6, $this->_model->getParentId());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testMoveWrongParent()
    {
        $this->_model->load(7);
        $this->_model->move(100, 0);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testMoveWrongId()
    {
        $this->_model->move(100, 0);
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories.php
     * @magentoAppIsolation enabled
     */
    public function testGetUrlPath()
    {
        $this->assertNull($this->_model->getUrlPath());
        $this->_model->load(4);
        $this->assertEquals('category-1/category-1-1', $this->_model->getUrlPath());
    }

    public function testGetParentId()
    {
        $this->assertEquals(0, $this->_model->getParentId());
        $this->_model->unsetData();
        $this->_model->load(4);
        $this->assertEquals(3, $this->_model->getParentId());
    }

    public function testGetParentIds()
    {
        $this->assertEquals([], $this->_model->getParentIds());
        $this->_model->unsetData();
        $this->_model->load(4);
        $this->assertContains(3, $this->_model->getParentIds());
        $this->assertNotContains(4, $this->_model->getParentIds());
    }

    public function testGetChildren()
    {
        $this->_model->load(3);
        $this->assertEquals(array_diff([4, 13], explode(',', $this->_model->getChildren())), []);
    }

    public function testGetChildrenSorted()
    {
        $this->_model->load(2);
        $unsorted = explode(',', $this->_model->getChildren());
        sort($unsorted);
        $this->assertEquals(array_diff($unsorted, explode(',', $this->_model->getChildren(true, true, true))), []);
    }

    public function testGetPathInStore()
    {
        $this->_model->load(5);
        $this->assertEquals('5,4,3', $this->_model->getPathInStore());
    }

    public function testGetAllChildren()
    {
        $this->_model->load(4);
        $this->assertEquals('4,5', $this->_model->getAllChildren());
        $this->_model->load(5);
        $this->assertEquals('5', $this->_model->getAllChildren());
    }

    public function testGetPathIds()
    {
        $this->assertEquals([''], $this->_model->getPathIds());
        $this->_model->setPathIds([1]);
        $this->assertEquals([1], $this->_model->getPathIds());

        $this->_model->unsetData();
        $this->_model->setPath('1/2/3');
        $this->assertEquals([1, 2, 3], $this->_model->getPathIds());
    }

    public function testGetLevel()
    {
        $this->assertEquals(0, $this->_model->getLevel());
        $this->_model->setData('level', 1);
        $this->assertEquals(1, $this->_model->getLevel());
    }

    public function testGetAnchorsAbove()
    {
        $this->_model->load(4);
        $this->assertContains(3, $this->_model->getAnchorsAbove());
        $this->_model->load(5);
        $this->assertContains(4, $this->_model->getAnchorsAbove());
    }

    public function testGetParentCategories()
    {
        $this->_model->load(5);
        $parents = $this->_model->getParentCategories();
        $this->assertEquals(3, count($parents));
    }

    public function testGetParentCategoriesEmpty()
    {
        $this->_model->load(1);
        $parents = $this->_model->getParentCategories();
        $this->assertEquals(0, count($parents));
    }

    public function testGetChildrenCategories()
    {
        $this->_model->load(3);
        $children = $this->_model->getChildrenCategories();
        $this->assertEquals(2, count($children));
    }

    public function testGetChildrenCategoriesEmpty()
    {
        $this->_model->load(5);
        $children = $this->_model->getChildrenCategories();
        $this->assertEquals(0, count($children));
    }

    public function testGetParentDesignCategory()
    {
        $this->_model->load(5);
        $parent = $this->_model->getParentDesignCategory();
        $this->assertEquals(5, $parent->getId());
    }

    public function testIsInRootCategoryList()
    {
        $this->assertFalse($this->_model->isInRootCategoryList());
        $this->_model->unsetData();
        $this->_model->load(3);
        $this->assertTrue($this->_model->isInRootCategoryList());
    }

    /**
     * Test correct partial reindex on enabling / disabling category.
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/category_reindexing.php
     */
    public function testReindexingOnIsActiveChange()
    {
        /* @var StoreManagerInterface $storeManager */
        $storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore(0);

        $categoryAId = 3;
        $categoryBId = 4;
        $categoryCId = 5;

        $categoryB = $this->_categoryRepository->get($categoryBId);
        $categoryC = $this->_categoryRepository->get($categoryCId);

        self::assertCount(0, $this->getCategoryProductIndexRecords($categoryAId));

        $categoryB->setIsActive(true)->save();
        self::assertCount(1, $this->getCategoryProductIndexRecords($categoryAId));

        $categoryC->setIsActive(true)->save();
        self::assertCount(1, $this->getCategoryProductIndexRecords($categoryAId));

        // We need to reload models because bug is present in CategoryRepository
        // which returns incorrect dataHasChangedFor value.
        $categoryB = $this->_objectManager->create(Category::class)->load($categoryBId);
        $categoryC = $this->_objectManager->create(Category::class)->load($categoryCId);

        $categoryB->setIsActive(false)->save();
        self::assertCount(1, $this->getCategoryProductIndexRecords($categoryAId));

        $categoryC->setIsActive(false)->save();
        self::assertCount(0, $this->getCategoryProductIndexRecords($categoryAId));
    }

    /**
     * @param int $categoryId
     * @return \Magento\Framework\DB\Select
     */
    private function getCategoryProductIndexRecords(int $categoryId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->_resource->getConnection();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $connection->select();
        $select
            ->from($this->_resource->getTableName($this->_indexer::MAIN_INDEX_TABLE))
            ->where('category_id = ?', $categoryId);
        return $select->query()->fetchAll();
    }
}
