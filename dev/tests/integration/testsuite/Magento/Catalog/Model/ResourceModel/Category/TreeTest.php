<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Category;

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
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->treeModel = $objectManager->create(Tree::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @return void
     */
    public function testLoadByIds(): void
    {
        $categoryId = 333;
        // Load category nodes by created category
        $this->treeModel->loadByIds([$categoryId]);
        $this->assertCount(3, $this->treeModel->getNodes());
        $this->assertInstanceOf(Node::class, $this->treeModel->getNodeById($categoryId));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_wrong_path.php
     * @return void
     */
    public function testLoadByIdsWithWrongGategoryPath(): void
    {
        $categoryId = 127;
        // Load category nodes by created category
        $this->treeModel->loadByIds([$categoryId]);
        $this->assertCount(2, $this->treeModel->getNodes());
        $this->assertNull($this->treeModel->getNodeById($categoryId));
    }
}
