<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Category;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for categories in the tree block.
 */
class TreeBlockTest extends AbstractBackendController
{

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = $this->_objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test disabled categories in the tree block.
     */
    #[
        AppArea('adminhtml'),
        DataFixture(CategoryFixture::class, as: 'c10'),
        DataFixture(CategoryFixture::class, ['parent_id' => '$c10.id$', 'is_active' => false], 'c11'),
    ]
    public function testDisabledCategoriesHtml()
    {
        $category = $this->fixtures->get('c10');
        $layout = $this->_objectManager->get(LayoutInterface::class);
        $categoryTreeBlock = $layout->createBlock(Tree::class);
        $categoryTreeArray = $categoryTreeBlock->getTree($category);
        $this->assertCount(1, $categoryTreeArray);
        $this->assertArrayHasKey('a_attr', $categoryTreeArray[0]);
        $this->assertArrayHasKey('class', $categoryTreeArray[0]['a_attr']);
        $this->assertStringContainsString('not-active-category', $categoryTreeArray[0]['a_attr']['class']);
    }
}
