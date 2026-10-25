<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var \Magento\Catalog\Model\Config\Source\Category
     */
    private $model;

    /**
     * @var Collection|MockObject
     */
    private $categoryCollection;

    /**
     * @var Category|MockObject
     */
    private $category;

    protected function setUp(): void
    {
        $this->categoryCollection = $this->createMock(Collection::class);

        $this->category = $this->createPartialMockWithReflection(
            Category::class,
            ['getName', 'getId']
        );

        $categoryCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $categoryCollectionFactory->method('create')->willReturn(
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
            'name'
        )->willReturn($this->categoryCollection);
        $this->categoryCollection->expects($this->once())->method('addRootLevelFilter')->willReturn(
            $this->categoryCollection
        );
        $this->categoryCollection->expects($this->once())->method('load');
        $this->categoryCollection->method('getIterator')->willReturn(
            new \ArrayIterator([$this->category])
        );

        $this->category->expects($this->once())->method('getName')->willReturn('name');
        $this->category->expects($this->once())->method('getId')->willReturn(3);

        $this->assertEquals($expect, $this->model->toOptionArray());
    }
}
