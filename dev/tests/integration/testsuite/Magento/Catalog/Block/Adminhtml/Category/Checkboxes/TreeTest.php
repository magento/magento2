<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Checkboxes;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class TreeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree */
    protected $block;

    protected function setUp(): void
    {
        $this->block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree::class
        );
    }

    public function testSetGetCategoryIds()
    {
        $this->block->setCategoryIds([1, 4, 7, 56, 2]);
        $this->assertEquals([1, 4, 7, 56, 2], $this->block->getCategoryIds());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetTreeJson()
    {
        $jsonTree = $this->block->getTreeJson();
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
}
