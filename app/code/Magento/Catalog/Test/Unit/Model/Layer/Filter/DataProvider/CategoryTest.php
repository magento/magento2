<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        /** @var \Magento\Framework\Registry $var */
        $this->coreRegistry = $var = $this->getMockBuilder('\Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['register'])
            ->getMock();
        $this->category = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'setStoreId', 'load', 'getPathIds'])
            ->getMock();
        $this->categoryFactory = $this->getMockBuilder('Magento\Catalog\Model\CategoryFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->categoryFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->category));
        $this->store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $this->layer = $this->getMockBuilder('Magento\Catalog\Model\Layer')
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentStore', 'getCurrentCategory'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getCurrentStore')
            ->will($this->returnValue($this->store));
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\Catalog\Model\Layer\Filter\DataProvider\Category',
            [
                'coreRegistry' => $this->coreRegistry,
                'categoryFactory' => $this->categoryFactory,
                'layer' => $this->layer,
            ]
        );
    }

    public function testGetCategoryWithAppliedId()
    {
        $storeId = 1234;
        $categoryId = 4321;
        $this->store->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($storeId));
        $this->layer->expects($this->any())
            ->method('getCurrentCategory')
            ->will($this->returnValue($this->category));
        $this->category->expects($this->once())
            ->method('setStoreId')
            ->with($this->equalTo($storeId))
            ->will($this->returnSelf());
        $this->category->expects($this->once())
            ->method('load')
            ->with($this->equalTo($categoryId))
            ->will($this->returnSelf());
        $this->category->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($categoryId));
        $this->category->expects($this->any())
            ->method('getPathIds')
            ->will($this->returnValue([20, 10]));
        $this->coreRegistry->expects($this->once())
            ->method('register')
            ->with(
                $this->equalTo('current_category_filter'),
                $this->equalTo($this->category),
                $this->equalTo(true)
            )
            ->will($this->returnSelf());
        $this->target->setCategoryId($categoryId);
        $this->assertSame($this->category, $this->target->getCategory());
        $this->assertSame(20, $this->target->getResetValue());

        return $this->target;
    }
}
