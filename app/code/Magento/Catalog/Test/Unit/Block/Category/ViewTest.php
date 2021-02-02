<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Category;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Category\View
     */
    protected $block;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(\Magento\Catalog\Block\Category\View::class, []);
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $categoryTag = ['catalog_category_1'];
        $currentCategoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);
        $currentCategoryMock->expects($this->once())->method('getIdentities')->willReturn($categoryTag);
        $this->block->setCurrentCategory($currentCategoryMock);
        $this->assertEquals($categoryTag, $this->block->getIdentities());
    }
}
