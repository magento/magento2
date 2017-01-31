<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\Price
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    private $itemDataBuilder;

    private $resource;

    /** @var \Magento\Catalog\Model\Layer\Filter\Dynamic\Auto|MockObject */
    private $algorithm;

    /**
     * @var \Magento\Catalog\Model\Price|MockObject
     */
    private $price;

    /**
     * @var \Magento\Catalog\Model\Layer|MockObject
     */
    private $layer;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price|MockObject
     */
    private $dataProvider;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Price
     */
    private $target;

    /** @var \Magento\Framework\App\RequestInterface|MockObject */
    private $request;

    /** @var  \Magento\Catalog\Model\Layer\Filter\ItemFactory|MockObject */
    private $filterItemFactory;

    /** @var  \Magento\Catalog\Model\Layer\State|MockObject */
    private $state;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $dataProviderFactory = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->dataProvider = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\DataProvider\Price')
            ->disableOriginalConstructor()
            ->setMethods(['setPriceId', 'getPrice', 'getResource'])
            ->getMock();
        $this->resource = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Layer\Filter\Price')
            ->disableOriginalConstructor()
            ->setMethods(['applyPriceRange'])
            ->getMock();
        $this->dataProvider->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($this->resource));

        $dataProviderFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->dataProvider));

        $this->layer = $this->getMockBuilder('\Magento\Catalog\Model\Layer')
            ->disableOriginalConstructor()
            ->setMethods(['getState'])
            ->getMock();

        $this->state = $this->getMockBuilder('\Magento\Catalog\Model\Layer\State')
            ->disableOriginalConstructor()
            ->setMethods(['addFilter'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getState')
            ->will($this->returnValue($this->state));

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

        $this->attribute = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getFrontend', 'getIsFilterable'])
            ->getMock();
        $algorithmFactory = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->algorithm = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Dynamic\Auto')
            ->disableOriginalConstructor()
            ->setMethods(['getItemsData'])
            ->getMock();

        $algorithmFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->algorithm));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\Catalog\Model\Layer\Filter\Price',
            [
                'dataProviderFactory' => $dataProviderFactory,
                'layer' => $this->layer,
                'itemDataBuilder' => $this->itemDataBuilder,
                'filterItemFactory' => $this->filterItemFactory,
                'escaper' => $escaper,
                'algorithmFactory' => $algorithmFactory
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

    /** @var  \Magento\Eav\Model\Entity\Attribute|MockObject */
    private $attribute;
    public function testApply()
    {
        $priceId = '15-50';
        $requestVar = 'test_request_var';

        $this->target->setRequestVar($requestVar);
        $this->request->expects($this->exactly(1))
            ->method('getParam')
            ->will(
                $this->returnCallback(
                    function ($field) use ($requestVar, $priceId) {
                        $this->assertTrue(in_array($field, [$requestVar, 'id']));
                        return $priceId;
                    }
                )
            );

        $this->target->apply($this->request);
    }

    public function testGetItems()
    {
        $this->target->setAttributeModel($this->attribute);
        $attributeCode = 'attributeCode';
        $this->attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));
        $this->algorithm->expects($this->any())
            ->method('getItemsData')
            ->will($this->returnValue([]));
        $this->target->getItems();
    }
}
