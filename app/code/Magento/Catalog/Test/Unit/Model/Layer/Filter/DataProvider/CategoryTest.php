<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Catalog\Model\Category|MockObject */
    private $category;

    /** @var  \Magento\Store\Model\Store|MockObject */
    private $store;

    /** @var  \Magento\Catalog\Model\Layer|MockObject */
    private $layer;

    /** @var  \Magento\Catalog\Model\CategoryFactory|MockObject */
    private $categoryFactory;

    /** @var  \Magento\Framework\Registry|MockObject */
    private $coreRegistry;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
     */
    private $target;

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setUp(): void
    {
        /** @var \Magento\Framework\Registry $var */
        $this->coreRegistry = $var = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['register'])
            ->getMock();
        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'setStoreId', 'load', 'getPathIds'])
            ->getMock();
        $this->categoryFactory = $this->getMockBuilder(\Magento\Catalog\Model\CategoryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->categoryFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->category);
        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $this->layer = $this->getMockBuilder(\Magento\Catalog\Model\Layer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentStore', 'getCurrentCategory'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getCurrentStore')
            ->willReturn($this->store);
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\Layer\Filter\DataProvider\Category::class,
            [
                'coreRegistry' => $this->coreRegistry,
                'categoryFactory' => $this->categoryFactory,
                'layer' => $this->layer,
            ]
        );
    }

    /**
     * @return \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
     */
    public function testGetCategoryWithAppliedId()
    {
        $storeId = 1234;
        $categoryId = 4321;
        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->layer->expects($this->any())
            ->method('getCurrentCategory')
            ->willReturn($this->category);
        $this->category->expects($this->once())
            ->method('setStoreId')
            ->with($this->equalTo($storeId))
            ->willReturnSelf();
        $this->category->expects($this->once())
            ->method('load')
            ->with($this->equalTo($categoryId))
            ->willReturnSelf();
        $this->category->expects($this->any())
            ->method('getId')
            ->willReturn($categoryId);
        $this->category->expects($this->any())
            ->method('getPathIds')
            ->willReturn([20, 10]);
        $this->coreRegistry->expects($this->once())
            ->method('register')
            ->with(
                $this->equalTo('current_category_filter'),
                $this->equalTo($this->category),
                $this->equalTo(true)
            )
            ->willReturnSelf();
        $this->target->setCategoryId($categoryId);
        $this->assertSame($this->category, $this->target->getCategory());
        $this->assertSame(20, $this->target->getResetValue());

        return $this->target;
    }
}
