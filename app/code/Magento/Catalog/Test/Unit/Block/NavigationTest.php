<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block;

class NavigationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Navigation
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $design;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContext;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $categoryFactory = $this->getMock(
            \Magento\Catalog\Model\CategoryFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->registry = $this->getMock(\Magento\Framework\Registry::class);
        $this->storeManager = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->design = $this->getMock(\Magento\Framework\View\DesignInterface::class);
        $this->httpContext = $this->getMock(\Magento\Framework\App\Http\Context::class, [], [], '', false);
        $this->block = $objectManager->getObject(
            \Magento\Catalog\Block\Navigation::class,
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
            [\Magento\Catalog\Model\Category::CACHE_TAG, \Magento\Store\Model\Group::CACHE_TAG],
            $this->block->getIdentities()
        );
    }

    public function testGetCurrentCategoryKey()
    {
        $categoryKey = 101;
        $category = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $category->expects($this->any())->method('getPath')->willReturn($categoryKey);

        $this->registry->expects($this->any())->method('registry')->with('current_category')->willReturn($category);

        $this->assertEquals($categoryKey, $this->block->getCurrentCategoryKey());
    }

    public function testGetCurrentCategoryKeyFromRootCategory()
    {
        $categoryKey = 102;
        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $store->expects($this->any())->method('getRootCategoryId')->willReturn($categoryKey);

        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->assertEquals($categoryKey, $this->block->getCurrentCategoryKey());
    }

    public function testGetCacheKeyInfo()
    {
        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $store->expects($this->atLeastOnce())->method('getId')->willReturn(55);
        $store->expects($this->atLeastOnce())->method('getRootCategoryId')->willReturn(60);

        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);

        $theme = $this->getMock(\Magento\Framework\View\Design\ThemeInterface::class);
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
