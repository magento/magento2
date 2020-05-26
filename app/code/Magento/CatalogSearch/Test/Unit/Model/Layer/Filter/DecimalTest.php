<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory;
use Magento\CatalogSearch\Model\Layer\Filter\Decimal;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogSearch\Model\Layer\Filter\Decimal
 */
class DecimalTest extends TestCase
{
    private $filterItem;

    /**
     * @var Collection|MockObject
     */
    private $fulltextCollection;

    /**
     * @var Layer|MockObject
     */
    private $layer;

    /**
     * @var Decimal
     */
    private $target;

    /** @var RequestInterface|MockObject */
    private $request;

    /** @var  State|MockObject */
    private $state;

    /** @var  ItemFactory|MockObject */
    private $filterItemFactory;

    /** @var  Attribute|MockObject */
    private $attribute;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->layer = $this->getMockBuilder(Layer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getState', 'getProductCollection'])
            ->getMock();
        $this->filterItemFactory = $this->getMockBuilder(
            ItemFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->filterItem = $this->getMockBuilder(
            Item::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->getMock();
        $this->filterItem->expects($this->any())
            ->method($this->anything())->willReturnSelf();
        $this->filterItemFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->filterItem);

        $this->fulltextCollection = $this->fulltextCollection = $this->getMockBuilder(
            Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->layer->expects($this->any())
            ->method('getProductCollection')
            ->willReturn($this->fulltextCollection);

        $filterDecimalFactory =
            $this->getMockBuilder(DecimalFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $resource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $filterDecimalFactory->expects($this->once())
            ->method('create')
            ->willReturn($resource);

        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getFrontend', 'getIsFilterable'])
            ->getMock();

        $this->state = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getState')
            ->willReturn($this->state);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            Decimal::class,
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

    public function testApply()
    {
        $filter = '10-150';
        $requestVar = 'test_request_var';

        $this->target->setRequestVar($requestVar);
        $this->request->expects($this->exactly(1))
            ->method('getParam')
            ->willReturnCallback(
                function ($field) use ($requestVar, $filter) {
                    $this->assertContains($field, [$requestVar, 'id']);
                    return $filter;
                }
            );

        $attributeCode = 'AttributeCode';
        $this->attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->fulltextCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with($attributeCode)->willReturnSelf();

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
