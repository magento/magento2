<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Test for \Magento\CatalogSearch\Model\Layer\Filter\Price
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    private $itemDataBuilder;

    /**
     * @var \Magento\Catalog\Model\Price|MockObject
     */
    private $price;

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

    /** @var  \Magento\Catalog\Model\Layer\State|MockObject */
    private $state;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $dataProviderFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->dataProvider = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\DataProvider\Price::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPriceId', 'getPrice'])
            ->getMock();

        $dataProviderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->dataProvider);

        $this->layer = $this->getMockBuilder(\Magento\Catalog\Model\Layer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getState', 'getProductCollection'])
            ->getMock();

        $this->state = $this->getMockBuilder(\Magento\Catalog\Model\Layer\State::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getState')
            ->willReturn($this->state);

        $this->fulltextCollection = $this->fulltextCollection = $this->getMockBuilder(
            \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getFacetedData'])
            ->getMock();

        $this->layer->expects($this->any())
            ->method('getProductCollection')
            ->willReturn($this->fulltextCollection);

        $this->itemDataBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\Item\DataBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addItemData', 'build'])
            ->getMock();

        $this->filterItemFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\Layer\Filter\ItemFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $filterItem = $this->getMockBuilder(
            \Magento\Catalog\Model\Layer\Filter\Item::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->getMock();
        $filterItem->expects($this->any())
            ->method($this->anything())
            ->willReturnSelf();
        $this->filterItemFactory->expects($this->any())
            ->method('create')
            ->willReturn($filterItem);

        $escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();
        $escaper->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getFrontend', 'getIsFilterable'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Layer\Filter\Price::class,
            [
                'dataProviderFactory' => $dataProviderFactory,
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
            ->willReturnCallback(
                
                    function ($field) use ($requestField, $idField, $requestValue, $idValue) {
                        switch ($field) {
                            case $requestField:
                                return $requestValue;
                            case $idField:
                                return $idValue;
                        }
                    }
                
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
            ->willReturnCallback(
                
                    function ($field) use ($requestVar, $priceId) {
                        $this->assertTrue(in_array($field, [$requestVar, 'id']));
                        return $priceId;
                    }
                
            );

        $this->fulltextCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('price')
            ->willReturnSelf();

        $this->target->setCurrencyRate(1);
        $this->target->apply($this->request);
    }

    public function testGetItems()
    {
        $this->target->setAttributeModel($this->attribute);

        $attributeCode = 'attributeCode';
        $this->attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->with($attributeCode)
            ->willReturn([]);
        $this->target->getItems();
    }
}
