<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Catalog\Category;

/**
 * Test for \Magento\UrlRewrite\Block\Catalog\Category\Tree
 *
 * @magentoAppArea adminhtml
 */
class TreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\UrlRewrite\Block\Catalog\Category\Tree
     */
    private $_treeBlock;

    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_treeBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\UrlRewrite\Block\Catalog\Category\Tree'
        );
    }

    /**
     * Test for method \Magento\UrlRewrite\Block\Catalog\Category\Tree::getTreeArray()
     */
    public function testGetTreeArray()
    {
        $expectedTreeArray = [
            'id' => 1,
            'parent_id' => 0,
            'children_count' => 1,
            'is_active' => false,
            'name' => 'Root',
            'level' => 0,
            'product_count' => 0,
            'children' => [
                [
                    'id' => 2,
                    'parent_id' => \Magento\Catalog\Model\Category::TREE_ROOT_ID,
                    'children_count' => 0,
                    'is_active' => true,
                    'name' => 'Default Category',
                    'level' => 1,
                    'product_count' => 0,
                    'cls' => 'active-category',
                    'expanded' => false,
                    'disabled' => true,
                ],
            ],
            'cls' => 'no-active-category',
            'expanded' => true,
        ];

        $this->assertEquals($expectedTreeArray, $this->_treeBlock->getTreeArray(), 'Tree array is invalid');
    }

    /**
     * Test prepare grid
     */
    public function testGetLoadTreeUrl()
    {
        $row = new \Magento\Framework\Object(['id' => 1]);
        $this->assertStringStartsWith(
            'http://localhost/index.php',
            $this->_treeBlock->getLoadTreeUrl($row),
            'Tree load URL is invalid'
        );
    }

    /**
     * Test for method \Magento\UrlRewrite\Block\Catalog\Category\Tree::getCategoryCollection()
     */
    public function testGetCategoryCollection()
    {
        $collection = $this->_treeBlock->getCategoryCollection();
        $this->assertInstanceOf('Magento\Catalog\Model\Resource\Category\Collection', $collection);
    }
}
