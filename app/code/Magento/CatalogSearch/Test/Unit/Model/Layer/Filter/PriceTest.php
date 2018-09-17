<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\CatalogSearch\Model\Layer\Filter\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder|MockObject
     */
    private $itemDataBuilder;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection|MockObject
     */
    private $fulltextCollection;

    /**
     * @var \Magento\Catalog\Model\Layer|MockObject
     */
    private $layer;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price|MockObject
     */
    private $dataProvider;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Filter\Price
     */
    private $target;

    /** @var \Magento\Framework\App\RequestInterface|MockObject */
    private $request;

    /** @var  \Magento\Catalog\Model\Layer\Filter\ItemFactory|MockObject */
    private $filterItemFactory;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|MockObject
     */
    private $storeMock;

    /** @var  \Magento\Catalog\Model\Layer\State|MockObject */
    private $state;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();

        $dataProviderFactory = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataProvider = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\DataProvider\Price')
            ->disableOriginalConstructor()
            ->setMethods(['setPriceId', 'getPrice'])
            ->getMock();
        $dataProviderFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->dataProvider));

        $this->layer = $this->getMockBuilder('\Magento\Catalog\Model\Layer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->state = $this->getMockBuilder('\Magento\Catalog\Model\Layer\State')
            ->disableOriginalConstructor()
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getState')
            ->will($this->returnValue($this->state));

        $this->fulltextCollection = $this->getMockBuilder(
            '\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection'
        )->disableOriginalConstructor()
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getProductCollection')
            ->will($this->returnValue($this->fulltextCollection));

        $this->itemDataBuilder = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Item\DataBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterItemFactory = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\ItemFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $filterItem = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Item')
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
            ->getMock();
        $escaper->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnArgument(0));

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods(['getCurrentCurrencyRate'])
            ->getMockForAbstractClass();
        $storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->attribute = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getFrontend', 'getIsFilterable'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\CatalogSearch\Model\Layer\Filter\Price',
            [
                'dataProviderFactory' => $dataProviderFactory,
                'layer' => $this->layer,
                'itemDataBuilder' => $this->itemDataBuilder,
                'filterItemFactory' => $this->filterItemFactory,
                'escaper' => $escaper,
                'storeManager' => $storeManagerMock,
            ]
        );
    }

    /**
     * @param $requestValue
     * @param $idValue
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

        $this->fulltextCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('price')
            ->will($this->returnSelf());

        $this->storeMock->expects($this->atLeastOnce())
            ->method('getCurrentCurrencyRate')
            ->willReturn(1);

        $this->target->apply($this->request);
    }

    public function testGetItems()
    {
        $this->target->setAttributeModel($this->attribute);

        $attributeCode = 'attributeCode';
        $this->attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->with($attributeCode)
            ->will($this->returnValue([]));
        $this->target->getItems();
    }
}
