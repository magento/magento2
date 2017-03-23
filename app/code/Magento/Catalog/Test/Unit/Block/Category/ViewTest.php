<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Category;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Category\View
     */
    protected $block;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(\Magento\Catalog\Block\Category\View::class, []);
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $categoryTag = ['catalog_category_1'];
        $currentCatogoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $currentCatogoryMock->expects($this->once())->method('getIdentities')->will($this->returnValue($categoryTag));
        $this->block->setCurrentCategory($currentCatogoryMock);
        $this->assertEquals($categoryTag, $this->block->getIdentities());
    }
}
