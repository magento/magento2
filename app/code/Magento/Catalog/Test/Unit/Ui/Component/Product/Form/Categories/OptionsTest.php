<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\Component\Product\Form\Categories;

use Magento\Catalog\Ui\Component\Product\Form\Categories\Options as CategoriesOptions;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\Category;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CategoriesOptions
     */
    protected $categoriesOptions;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var CategoryCollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryCollectionFactoryMock;

    /**
     * @var array
     */
    protected $categoryValueMap = [
        'id' => 'getId',
        Category::KEY_PARENT_ID => 'getParentId',
        Category::KEY_NAME => 'getName',
        Category::KEY_PATH => 'getPath',
        Category::KEY_IS_ACTIVE => 'getIsActive'
    ];

    protected function setUp(): void
    {
        $this->categoryCollectionFactoryMock = $this->getMockBuilder(CategoryCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->categoriesOptions = $this->objectManagerHelper->getObject(
            CategoriesOptions::class,
            ['categoryCollectionFactory' => $this->categoryCollectionFactoryMock]
        );
    }

    public function testToOptionArray()
    {
        $matchingNamesCollection = $this->getCategoryCollectionMock(
            [
                $this->getCategoryMock(['path' => '1/2']),
                $this->getCategoryMock(['path' => '1/3']),
                $this->getCategoryMock(['path' => '1/3/4']),
                $this->getCategoryMock(['path' => '1/2/5']),
                $this->getCategoryMock(['path' => '1/3/4/6']),
                $this->getCategoryMock(['path' => '1/7']),
                $this->getCategoryMock(['path' => '1/8']),
                $this->getCategoryMock(['path' => '1/7/9'])
            ]
        );

        $collection = $this->getCategoryCollectionMock(
            [
                $this->getCategoryMock(['id' => '2', 'parent_id' => '1', 'name' => 'Category 2', 'is_active' => '1']),
                $this->getCategoryMock(['id' => '3', 'parent_id' => '1', 'name' => 'Category 3', 'is_active' => '0']),
                $this->getCategoryMock(['id' => '4', 'parent_id' => '3', 'name' => 'Category 4', 'is_active' => '1']),
                $this->getCategoryMock(['id' => '5', 'parent_id' => '2', 'name' => 'Category 5', 'is_active' => '1']),
                $this->getCategoryMock(['id' => '6', 'parent_id' => '4', 'name' => 'Category 6', 'is_active' => '1']),
                $this->getCategoryMock(['id' => '7', 'parent_id' => '1', 'name' => 'Category 7', 'is_active' => '0']),
                $this->getCategoryMock(['id' => '8', 'parent_id' => '1', 'name' => 'Category 8', 'is_active' => '1']),
                $this->getCategoryMock(['id' => '9', 'parent_id' => '7', 'name' => 'Category 9', 'is_active' => '1']),
            ]
        );

        $result = [
            [
                'value' => '2',
                'is_active' => '1',
                'label' => 'Category 2',
                'optgroup' => [
                    [
                        'value' => '5',
                        'is_active' => '1',
                        'label' => 'Category 5'
                    ]
                ]
            ],
            [
                'value' => '3',
                'is_active' => '0',
                'label' => 'Category 3',
                'optgroup' => [
                    [
                        'value' => '4',
                        'is_active' => '1',
                        'label' => 'Category 4',
                        'optgroup' => [
                            [
                                'value' => '6',
                                'is_active' => '1',
                                'label' => 'Category 6'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'value' => '7',
                'is_active' => '0',
                'label' => 'Category 7',
                'optgroup' => [
                    [
                        'value' => '9',
                        'is_active' => '1',
                        'label' => 'Category 9'
                    ]
                ]
            ],
            [
                'value' => '8',
                'is_active' => '1',
                'label' => 'Category 8'
            ]
        ];

        $this->categoryCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnOnConsecutiveCalls($matchingNamesCollection, $collection);

        $this->assertSame($result, $this->categoriesOptions->toOptionArray());
    }

    /**
     * @param array $categories
     * @return CategoryCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCategoryCollectionMock($categories)
    {
        $categoryCollectionMock = $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $categoryCollectionMock->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $categoryCollectionMock->expects($this->any())
            ->method('addAttributeToFilter')
            ->willReturnSelf();
        $categoryCollectionMock->expects($this->any())
            ->method('setStoreId')
            ->willReturnSelf();

        $categoryCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($categories));

        return $categoryCollectionMock;
    }

    /**
     * @param array $data
     * @return Category|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCategoryMock($data)
    {
        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($this->categoryValueMap as $index => $method) {
            if (array_key_exists($index, $data)) {
                $categoryMock->expects($this->any())
                    ->method($method)
                    ->willReturn($data[$index]);
            }
        }

        return $categoryMock;
    }
}
