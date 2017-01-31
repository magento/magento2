<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CategoryUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $canonicalUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $currentUrlRewritesRegenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $childrenUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeViewService;

    /** @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject */
    protected $category;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryRepository;

    /**
     * Test method
     */
    protected function setUp()
    {
        $this->currentUrlRewritesRegenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator'
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->childrenUrlRewriteGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->storeViewService = $this->getMockBuilder('Magento\CatalogUrlRewrite\Service\V1\StoreViewService')
            ->disableOriginalConstructor()->getMock();
        $this->category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $this->categoryRepository = $this->getMock('Magento\Catalog\Api\CategoryRepositoryInterface');

        $this->categoryUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator',
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'childrenUrlRewriteGenerator' => $this->childrenUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'storeViewService' => $this->storeViewService,
                'categoryRepository' => $this->categoryRepository,
            ]
        );
    }

    /**
     * Test method
     */
    public function testGenerationForGlobalScope()
    {
        $this->category->expects($this->any())->method('getStoreId')->will($this->returnValue(null));
        $this->category->expects($this->any())->method('getStoreIds')->will($this->returnValue([1]));
        $this->storeViewService->expects($this->once())->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(false));
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $children = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $children->setTargetPath('category-2')
            ->setStoreId(2);
        $this->childrenUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$children]));
        $current = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $current->setTargetPath('category-3')
            ->setStoreId(3);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$current]));
        $categoryForSpecificStore = $this->getMock(
            'Magento\Catalog\Model\Category',
            ['getUrlKey', 'getUrlPath'],
            [],
            '',
            false
        );
        $this->categoryRepository->expects($this->once())->method('get')->willReturn($categoryForSpecificStore);

        $this->assertEquals(
            [$canonical, $children, $current],
            $this->categoryUrlRewriteGenerator->generate($this->category)
        );
    }

    /**
     * Test method
     */
    public function testGenerationForSpecificStore()
    {
        $this->category->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $this->category->expects($this->never())->method('getStoreIds');
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $this->childrenUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals([$canonical], $this->categoryUrlRewriteGenerator->generate($this->category));
    }

    /**
     * Test method
     */
    public function testSkipGenerationForGlobalScope()
    {
        $this->category->expects($this->any())->method('getStoreIds')->will($this->returnValue([1, 2]));
        $this->storeViewService->expects($this->exactly(2))->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(true));

        $this->assertEquals([], $this->categoryUrlRewriteGenerator->generate($this->category));
    }
}
