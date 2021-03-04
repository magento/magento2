<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\Category
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection|MockObject
     */
    private $categoryCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category|MockObject
     */
    private $category;

    protected function setUp(): void
    {
        $this->categoryCollection = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category::class)
            ->setMethods(['getName', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory|MockObject $categoryCollectionFactory
         */
        $categoryCollectionFactory =
            $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryCollectionFactory->expects($this->any())->method('create')->willReturn(
            $this->categoryCollection
        );

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Config\Source\Category::class,
            ['categoryCollectionFactory' => $categoryCollectionFactory]
        );
    }

    public function testToOptionArray()
    {
        $expect = [
            ['label' => __('-- Please Select a Category --'), 'value' => ''],
            ['label' => 'name', 'value' => 3],
        ];

        $this->categoryCollection->expects($this->once())->method('addAttributeToSelect')->with(
            $this->equalTo('name')
        )->willReturn($this->categoryCollection);
        $this->categoryCollection->expects($this->once())->method('addRootLevelFilter')->willReturn(
            $this->categoryCollection
        );
        $this->categoryCollection->expects($this->once())->method('load');
        $this->categoryCollection->expects($this->any())->method('getIterator')->willReturn(
            new \ArrayIterator([$this->category])
        );

        $this->category->expects($this->once())->method('getName')->willReturn('name');
        $this->category->expects($this->once())->method('getId')->willReturn(3);

        $this->assertEquals($expect, $this->model->toOptionArray());
    }
}
