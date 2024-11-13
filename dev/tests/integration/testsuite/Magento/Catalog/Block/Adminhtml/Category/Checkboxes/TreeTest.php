<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Category\Checkboxes;

use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks category chooser block behaviour
 *
 * @see \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class TreeTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Tree */
    private $block;

    /** @var SerializerInterface */
    private $json;

    /** @var DefaultCategory */
    private $defaultCategoryHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Tree::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->defaultCategoryHelper = $this->objectManager->get(DefaultCategory::class);
    }

    /**
     * @return void
     */
    public function testSetGetCategoryIds(): void
    {
        $this->block->setCategoryIds([1, 4, 7, 56, 2]);
        $this->assertEquals([1, 4, 7, 56, 2], $this->block->getCategoryIds());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     *
     * @return void
     */
    public function testGetTreeJson(): void
    {
        $tablePrefix = $this->objectManager->create(ResourceConnection::class)->getTablePrefix();
        $categoryTable = 'catalog_category_product_index';
        $categoryTable = $tablePrefix ? $tablePrefix . $categoryTable : $categoryTable;

        $categoryFactoryMock = $this->getMockBuilder(CategoryFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $categoryMock = $this->getMockBuilder(Category::class)
            ->onlyMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        // create resource connection mock
        $resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->onlyMethods(['getTableName'])
            ->disableOriginalConstructor()
            ->getMock();

        $resourceConnection->method('getTableName')
            ->willReturn($categoryTable);

        $categoryFactoryMock->method('create')
            ->willReturn($categoryMock);

        $categoryCollection = $this->objectManager->create(
            Collection::class,
            [
                'resource' => $resourceConnection
            ]
        );

        $categoryMock->method('getCollection')
            ->willReturn($categoryCollection);

        $treeObject = $this->objectManager->create(
            Tree::class,
            [
                'categoryFactory' => $categoryFactoryMock
            ]
        );

        $jsonTree = $treeObject->getTreeJson();

        $this->assertStringContainsString('Default Category (4)', $jsonTree);
        $this->assertStringContainsString('Category 1.1 (2)', $jsonTree);
        $this->assertStringContainsString('Category 1.1.1 (1)', $jsonTree);
        $this->assertStringContainsString('Category 2 (0)', $jsonTree);
        $this->assertStringContainsString('Movable (0)', $jsonTree);
        $this->assertStringContainsString('Movable Position 1 (0)', $jsonTree);
        $this->assertStringContainsString('Movable Position 2 (2)', $jsonTree);
        $this->assertStringContainsString('Movable Position 3 (2)', $jsonTree);
        $this->assertStringContainsString('Category 12 (2)', $jsonTree);
        $this->assertStringMatchesFormat('%s"path":"1\/2\/%s\/%s\/%s"%s', $jsonTree);
    }

    /**
     * @return void
     */
    public function testGetTreeJsonWithSelectedCategory(): void
    {
        $this->block->setCategoryIds($this->defaultCategoryHelper->getId());
        $result = $this->json->unserialize($this->block->getTreeJson());
        $item = reset($result);
        $this->assertNotEmpty($item);
        $this->assertStringContainsString('Default Category', $item['text']);
        $this->assertTrue($item['checked']);
    }
}
