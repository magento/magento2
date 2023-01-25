<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleOptimizer\Test\Unit\Block\Code;

use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Block\Code\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    /**
     * @var Category
     */
    protected $block;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->registry = $this->createMock(Registry::class);
        $this->block = $objectManager->getObject(
            Category::class,
            ['registry' => $this->registry]
        );
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $categoryTags = ['catalog_category_1'];
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->once())->method('getIdentities')->willReturn($categoryTags);
        $this->registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_category'
        )->willReturn(
            $category
        );
        $this->assertEquals($categoryTags, $this->block->getIdentities());
    }
}
