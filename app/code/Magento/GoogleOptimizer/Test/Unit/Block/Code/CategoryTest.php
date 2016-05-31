<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Block\Code;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GoogleOptimizer\Block\Code\Category
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->block = $objectManager->getObject(
            'Magento\GoogleOptimizer\Block\Code\Category',
            ['registry' => $this->registry]
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $categoryTags = ['catalog_category_1'];
        $category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $category->expects($this->once())->method('getIdentities')->will($this->returnValue($categoryTags));
        $this->registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_category'
        )->will(
            $this->returnValue($category)
        );
        $this->assertEquals($categoryTags, $this->block->getIdentities());
    }
}
