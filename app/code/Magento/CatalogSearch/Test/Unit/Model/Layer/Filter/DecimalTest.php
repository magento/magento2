<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\CatalogSearch\Model\Layer\Filter\Decimal
 */
class DecimalTest extends \PHPUnit_Framework_TestCase
{
    private $filterItem;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection|MockObject
     */
    private $fulltextCollection;

    /**
     * @var \Magento\Catalog\Model\Layer|MockObject
     */
    private $layer;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Filter\Decimal
     */
    private $target;

    /** @var \Magento\Framework\App\RequestInterface|MockObject */
    private $request;

    /** @var  \Magento\Catalog\Model\Layer\State|MockObject */
    private $state;

    /** @var  \Magento\Catalog\Model\Layer\Filter\ItemFactory|MockObject */
    private $filterItemFactory;

    /** @var  \Magento\Eav\Model\Entity\Attribute|MockObject */
    private $attribute;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->layer = $this->getMockBuilder('\Magento\Catalog\Model\Layer')
            ->disableOriginalConstructor()
            ->setMethods(['getState', 'getProductCollection'])
            ->getMock();
        $this->filterItemFactory = $this->getMockBuilder(
            '\Magento\Catalog\Model\Layer\Filter\ItemFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->filterItem = $this->getMockBuilder(
            '\Magento\Catalog\Model\Layer\Filter\Item'
        )
            ->disableOriginalConstructor()
            ->setMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->getMock();
        $this->filterItem->expects($this->any())
            ->method($this->anything())
            ->will($this->returnSelf());
        $this->filterItemFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->filterItem));

        $this->fulltextCollection = $this->fulltextCollection = $this->getMockBuilder(
            '\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->layer->expects($this->any())
            ->method('getProductCollection')
            ->will($this->returnValue($this->fulltextCollection));

        $filterDecimalFactory =
            $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resource = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $filterDecimalFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resource));

        $this->attribute = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getFrontend', 'getIsFilterable'])
            ->getMock();

        $this->state = $this->getMockBuilder('\Magento\Catalog\Model\Layer\State')
            ->disableOriginalConstructor()
            ->setMethods(['addFilter'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getState')
            ->will($this->returnValue($this->state));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\CatalogSearch\Model\Layer\Filter\Decimal',
            [
                'filterItemFactory' => $this->filterItemFactory,
                'layer' => $this->layer,
                'filterDecimalFactory' => $filterDecimalFactory,
            ]
        );

        $this->target->setAttributeModel($this->attribute);
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
        $filter = '10-150';
        $requestVar = 'test_request_var';

        $this->target->setRequestVar($requestVar);
        $this->request->expects($this->exactly(1))
            ->method('getParam')
            ->will(
                $this->returnCallback(
                    function ($field) use ($requestVar, $filter) {
                        $this->assertTrue(in_array($field, [$requestVar, 'id']));
                        return $filter;
                    }
                )
            );

        $attributeCode = 'AttributeCode';
        $this->attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));

        $this->fulltextCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with($attributeCode)
            ->will($this->returnSelf());

        $this->target->apply($this->request);
    }

    public function testItemData()
    {
        $this->fulltextCollection->expects($this->any())
            ->method('getSize')
            ->willReturn(5);

        $this->fulltextCollection->expects($this->any())
            ->method('getFacetedData')
            ->willReturn([
                '2_10' => ['count' => 5],
                '*_*' => ['count' => 2]
            ]);
        $this->assertEquals(
            [
                $this->filterItem
            ],
            $this->target->getItems()
        );
    }
}
