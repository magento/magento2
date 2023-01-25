<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Category;

use Magento\Catalog\Block\Category\View;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $block;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(View::class, []);
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $categoryTag = ['catalog_category_1'];
        $currentCategoryMock = $this->createMock(Category::class);
        $currentCategoryMock->expects($this->once())->method('getIdentities')->willReturn($categoryTag);
        $this->block->setCurrentCategory($currentCategoryMock);
        $this->assertEquals($categoryTag, $this->block->getIdentities());
    }
}
