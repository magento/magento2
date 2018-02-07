<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\CatalogSearch\Model\Layer\Filter\Category
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
{
    private $itemDataBuilder;

    /**
     * @var \Magento\Catalog\Model\Category|MockObject
     */
    private $category;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection|MockObject
     */
    private $fulltextCollection;

    /**
     * @var \Magento\Catalog\Model\Layer|MockObject
     */
    private $layer;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Category|MockObject
     */
    private $dataProvider;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Filter\Category
     */
    private $target;

    /** @var \Magento\Framework\App\RequestInterface|MockObject */
    private $request;

    /** @var  \Magento\Catalog\Model\Layer\Filter\ItemFactory|MockObject */
    private $filterItemFactory;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $dataProviderFactory = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->dataProvider = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\DataProvider\Category')
            ->disableOriginalConstructor()
            ->setMethods(['setCategoryId', 'getCategory'])
            ->getMock();

        $dataProviderFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->dataProvider));

        $this->category = $this->getMockBuilder('\Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getChildrenCategories', 'getIsActive'])
            ->getMock();

        $this->dataProvider->expects($this->any())
            ->method('getCategory', 'isValid')
            ->will($this->returnValue($this->category));

        $this->layer = $this->getMockBuilder('\Magento\Catalog\Model\Layer')
            ->disableOriginalConstructor()
            ->setMethods(['getState', 'getProductCollection'])
            ->getMock();

        $this->fulltextCollection = $this->fulltextCollection = $this->getMockBuilder(
            '\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection'
        )
            ->disableOriginalConstructor()
            ->setMethods(['addCategoryFilter', 'getFacetedData', 'getSize'])
            ->getMock();

        $this->layer->expects($this->any())
            ->method('getProductCollection')
            ->will($this->returnValue($this->fulltextCollection));

        $this->itemDataBuilder = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Item\DataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['addItemData', 'build'])
            ->getMock();

        $this->filterItemFactory = $this->getMockBuilder(
            '\Magento\Catalog\Model\Layer\Filter\ItemFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $filterItem = $this->getMockBuilder(
            '\Magento\Catalog\Model\Layer\Filter\Item'
        )
            ->disableOriginalConstructor()
            ->setMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->getMock();
        $filterItem->expects($this->any())
            ->method($this->anything())
            ->will($this->returnSelf());
        $this->filterItemFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($filterItem));

        $escaper = $this->getMockBuilder('\Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();
        $escaper->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnArgument(0));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\CatalogSearch\Model\Layer\Filter\Category',
            [
                'categoryDataProviderFactory' => $dataProviderFactory,
                'layer' => $this->layer,
                'itemDataBuilder' => $this->itemDataBuilder,
                'filterItemFactory' => $this->filterItemFactory,
                'escaper' => $escaper,
            ]
        );
    }

    /**
     * @param $requestValue
     * @param $idValue
     * @param $isIdUsed
     * @dataProvider applyWithEmptyRequestDataProvider
     */
    public function testApplyWithEmptyRequest($requestValue, $idValue)
    {
        $requestField = 'test_request_var';
        $idField = 'id';

        $this->target->setRequestVar($requestField);

        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with($requestField)
            ->will(
                $this->returnCallback(
                    function ($field) use ($requestField, $idField, $requestValue, $idValue) {
                        switch ($field) {
                            case $requestField:
                                return $requestValue;
                            case $idField:
                                return $idValue;
                        }
                    }
                )
            );

        $result = $this->target->apply($this->request);
        $this->assertSame($this->target, $result);
    }

    /**
     * @return array
     */
    public function applyWithEmptyRequestDataProvider()
    {
        return [
            [
                'requestValue' => null,
                'id' => 0,
            ],
            [
                'requestValue' => 0,
                'id' => false,
            ],
            [
                'requestValue' => 0,
                'id' => null,
            ]
        ];
    }

    public function testApply()
    {
        $categoryId = 123;
        $requestVar = 'test_request_var';

        $this->target->setRequestVar($requestVar);
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->will(
                $this->returnCallback(
                    function ($field) use ($requestVar, $categoryId) {
                        $this->assertTrue(in_array($field, [$requestVar, 'id']));
                        return $categoryId;
                    }
                )
            );

        $this->dataProvider->expects($this->once())
            ->method('setCategoryId')
            ->with($categoryId)
            ->will($this->returnSelf());

        $this->category->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($categoryId));

        $this->fulltextCollection->expects($this->once())
            ->method('addCategoryFilter')
            ->with($this->category)
            ->will($this->returnSelf());

        $this->target->apply($this->request);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetItems()
    {
        $this->category->expects($this->any())
            ->method('getIsActive')
            ->will($this->returnValue(true));

        $category1 = $this->getMockBuilder('\Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getName', 'getIsActive'])
            ->getMock();
        $category1->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue(120));
        $category1->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Category 1'));
        $category1->expects($this->once())
            ->method('getIsActive')
            ->will($this->returnValue(true));

        $category2 = $this->getMockBuilder('\Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getName', 'getIsActive'])
            ->getMock();
        $category2->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue(5641));
        $category2->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Category 2'));
        $category2->expects($this->once())
            ->method('getIsActive')
            ->will($this->returnValue(true));

        $category3 = $this->getMockBuilder('\Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getName', 'getIsActive'])
            ->getMock();
        $category3->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue(777));
        $category3->expects($this->never())
            ->method('getName');
        $category3->expects($this->once())
            ->method('getIsActive')
            ->will($this->returnValue(true));

        $categories = [
            $category1,
            $category2,
            $category3,
        ];
        $this->category->expects($this->once())
            ->method('getChildrenCategories')
            ->will($this->returnValue($categories));

        $facetedData = [
            120 => ['count' => 10],
            5641 => ['count' => 45],
            777 => ['count' => 80],
        ];

        $this->fulltextCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(50));

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->with('category')
            ->will($this->returnValue($facetedData));

        $builtData = [
            [
                'label' => 'Category 1',
                'value' => 120,
                'count' => 10,
            ],
            [
                'label' => 'Category 2',
                'value' => 5641,
                'count' => 45,
            ],
        ];

        $this->itemDataBuilder->expects($this->at(0))
            ->method('addItemData')
            ->with(
                'Category 1',
                120,
                10
            )
            ->will($this->returnSelf());
        $this->itemDataBuilder->expects($this->at(1))
            ->method('addItemData')
            ->with(
                'Category 2',
                5641,
                45
            )
            ->will($this->returnSelf());
        $this->itemDataBuilder->expects($this->once())
            ->method('build')
            ->will($this->returnValue($builtData));

        $this->target->getItems();
    }
}
