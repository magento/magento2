<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block;

use Magento\Catalog\Block\Navigation;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NavigationTest extends TestCase
{
    /**
     * @var Navigation
     */
    protected $block;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var DesignInterface|MockObject
     */
    protected $design;

    /**
     * @var Context|MockObject
     */
    protected $httpContext;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $categoryFactory = $this->createPartialMock(CategoryFactory::class, ['create']);
        $this->registry = $this->createMock(Registry::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->design = $this->getMockForAbstractClass(DesignInterface::class);
        $this->httpContext = $this->createMock(Context::class);
        $this->block = $objectManager->getObject(
            Navigation::class,
            [
                'categoryFactory' => $categoryFactory,
                'registry' => $this->registry,
                'storeManager' => $this->storeManager,
                'design' => $this->design,
                'httpContext' => $this->httpContext
            ]
        );
    }

    public function testGetIdentities()
    {
        $this->assertEquals(
            [Category::CACHE_TAG, Group::CACHE_TAG],
            $this->block->getIdentities()
        );
    }

    public function testGetCurrentCategoryKey()
    {
        $categoryKey = 101;
        $category = $this->createMock(Category::class);
        $category->expects($this->any())->method('getPath')->willReturn($categoryKey);

        $this->registry->expects($this->any())->method('registry')->with('current_category')->willReturn($category);

        $this->assertEquals($categoryKey, $this->block->getCurrentCategoryKey());
    }

    public function testGetCurrentCategoryKeyFromRootCategory()
    {
        $categoryKey = 102;
        $store = $this->createMock(Store::class);
        $store->expects($this->any())->method('getRootCategoryId')->willReturn($categoryKey);

        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->assertEquals($categoryKey, $this->block->getCurrentCategoryKey());
    }

    public function testGetCacheKeyInfo()
    {
        $store = $this->createMock(Store::class);
        $store->expects($this->atLeastOnce())->method('getId')->willReturn(55);
        $store->expects($this->atLeastOnce())->method('getRootCategoryId')->willReturn(60);

        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);

        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->atLeastOnce())->method('getId')->willReturn(65);

        $this->design->expects($this->atLeastOnce())->method('getDesignTheme')->willReturn($theme);

        $this->httpContext->expects($this->atLeastOnce())
            ->method('getValue')
            ->with(\Magento\Customer\Model\Context::CONTEXT_GROUP)
            ->willReturn(70);

        $this->block->setTemplate('block_template');
        $this->block->setNameInLayout('block_name');

        $expectedResult = [
            'CATALOG_NAVIGATION',
            55,
            65,
            70,
            'template' => 'block_template',
            'name' => 'block_name',
            60,
            'category_path' => 60,
            'short_cache_id' => 'c3de6d1160d1e7730b04d6cad409a2b4'
        ];

        $this->assertEquals($expectedResult, $this->block->getCacheKeyInfo());
    }
}
