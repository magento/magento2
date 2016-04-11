<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Checkboxes;

/**
 * @magentoAppArea adminhtml
 */
class TreeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree'
        );
    }

    public function testSetGetCategoryIds()
    {
        $this->_block->setCategoryIds([1, 4, 7, 56, 2]);
        $this->assertEquals([1, 4, 7, 56, 2], $this->_block->getCategoryIds());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetTreeJson()
    {
        $jsonTree = $this->_block->getTreeJson();
        $this->assertContains('Default Category (1)', $jsonTree);
        $this->assertContains('Category 1.1 (2)', $jsonTree);
        $this->assertContains('Category 1.1.1 (1)', $jsonTree);
        $this->assertContains('Category 2 (0)', $jsonTree);
        $this->assertContains('Movable (0)', $jsonTree);
        $this->assertContains('Movable Position 1 (0)', $jsonTree);
        $this->assertContains('Movable Position 2 (2)', $jsonTree);
        $this->assertContains('Movable Position 3 (2)', $jsonTree);
        $this->assertContains('Category 12 (2)', $jsonTree);
        $this->assertContains('"path":"1\/2\/3\/4\/5"', $jsonTree);
    }
}
