<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryList;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Tree\Node;

/**
 * Test for \Magento\Catalog\Model\ResourceModel\Category\Tree.
 *
 * @magentoDbIsolation enabled
 */
class TreeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Tree
     */
    private $treeModel;

    /**
     * @var CategoryList
     */
    private $categoryList;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->treeModel = $objectManager->create(Tree::class);
        $this->categoryList = $objectManager->get(CategoryList::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @return void
     */
    public function testLoadByIds(): void
    {
        $categoryId = $this->getCategoryIdByName('Category 1');
        // Load category nodes by created category
        $this->treeModel->loadByIds([$categoryId]);
        $this->assertCount(3, $this->treeModel->getNodes());
        $this->assertInstanceOf(Node::class, $this->treeModel->getNodeById($categoryId));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_wrong_path.php
     * @return void
     */
    public function testLoadByIdsWithWrongCategoryPath(): void
    {
        $categoryId = $this->getCategoryIdByName('Category With Wrong Path');
        // Load category nodes by created category
        $this->treeModel->loadByIds([$categoryId]);
        $this->assertCount(1, $this->treeModel->getNodes());
        $this->assertNull($this->treeModel->getNodeById($categoryId));
    }

    /**
     * Return category id by category name.
     *
     * @param string $categoryName
     * @return int
     */
    private function getCategoryIdByName(string $categoryName): int
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CategoryInterface::KEY_NAME, $categoryName)
            ->create();
        $categories = $this->categoryList->getList($searchCriteria)->getItems();
        $category = reset($categories);
        $categoryId = $category->getId();

        return (int)$categoryId;
    }
}
