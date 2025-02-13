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
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogSearch\Model\Layer\Filter\Decimal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DecimalTest extends TestCase
{
    /**
     * @var Item|MockObject
     */
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
    private static $target;

    /**
     * @var RequestInterface|MockObject
     */
    private static $request;

    /**
     * @var State|MockObject
     */
    private $state;

    /**
     * @var ItemFactory|MockObject
     */
    private $filterItemFactory;

    /**
     * @var Attribute|MockObject
     */
    private $attribute;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        self::$request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->layer = $this->getMockBuilder(Layer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getState', 'getProductCollection'])
            ->getMock();
        $this->filterItemFactory = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->filterItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->getMock();
        $this->filterItem->expects($this->any())
            ->method($this->anything())->willReturnSelf();
        $this->filterItemFactory->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function (array $data) {
                    return new Item(
                        $this->createMock(\Magento\Framework\UrlInterface::class),
                        $this->createMock(\Magento\Theme\Block\Html\Pager::class),
                        $data
                    );
                }
            );

        $this->fulltextCollection = $this->getMockBuilder(
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
                ->onlyMethods(['create'])
                ->getMock();
        $resource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();
        $filterDecimalFactory->expects($this->once())
            ->method('create')
            ->willReturn($resource);

        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributeCode', 'getFrontend'])
            ->addMethods(['getIsFilterable'])
            ->getMock();

        $this->state = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFilter'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getState')
            ->willReturn($this->state);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $priceFormatter = $this->createMock(PriceCurrencyInterface::class);
        $priceFormatter->method('format')
            ->willReturnCallback(
                function ($number) {
                    return sprintf('$%01.2f', $number);
                }
            );
        self::$target = $objectManagerHelper->getObject(
            Decimal::class,
            [
                'filterItemFactory' => $this->filterItemFactory,
                'layer' => $this->layer,
                'filterDecimalFactory' => $filterDecimalFactory,
                'priceCurrency' => $priceFormatter,
            ]
        );

        self::$target->setAttributeModel($this->attribute);
    }

    /**
     * @param int|null $requestValue
     * @param int|null|bool $idValue
     *
     * @return void
     * @dataProvider applyWithEmptyRequestDataProvider
     */
    public static function testApplyWithEmptyRequest(?int $requestValue, $idValue): void
    {
        $requestField = 'test_request_var';
        $idField = 'id';

        self::$target->setRequestVar($requestField);

        self::$request
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

        $result = self::$target->apply(self::$request);
        self::assertSame(self::$target, $result);
    }

    /**
     * @return array
     */
    public static function applyWithEmptyRequestDataProvider(): array
    {
        return [
            [
                'requestValue' => null,
                'idValue' => 0
            ],
            [
                'requestValue' => 0,
                'idValue' => false
            ],
            [
                'requestValue' => 0,
                'idValue' => null
            ]
        ];
    }

    /**
     * @return void
     */
    public function testApply(): void
    {
        $filter = '10-150';
        $requestVar = 'test_request_var';

        self::$target->setRequestVar($requestVar);
        self::$request->expects($this->exactly(1))
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

        self::$target->apply(self::$request);
    }

    /**
     * @param array $facets
     * @param array $expected
     * @dataProvider itemDataDataProvider
     * @return void
     */
    public function testItemData(array $facets, array $expected): void
    {
        $this->fulltextCollection->expects($this->any())
            ->method('getSize')
            ->willReturn(5);

        $this->fulltextCollection->expects($this->any())
            ->method('getFacetedData')
            ->willReturn($facets);
        $actual = [];
        foreach (self::$target->getItems() as $item) {
            $actual[] = ['label' => $item->getLabel(), 'value' => $item->getValue(), 'count' => $item->getCount()];
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function itemDataDataProvider(): array
    {
        return [
            [
                [
                    '0_10' => ['count' => 5],
                    '10_20' => ['count' => 2],
                    '30_' => ['count' => 1]
                ],
                [
                    ['label' => '$10.00 - $19.99', 'value' => '10-20', 'count' => '2'],
                    ['label' => '$30.00 and above', 'value' => '30-', 'count' => '1'],
                ]
            ],
            [
                [
                    '*_100' => ['count' => 3],
                    '200_*' => ['count' => 1],
                ],
                [
                    ['label' => '$0.00 - $99.99', 'value' => '-100', 'count' => '3'],
                    ['label' => '$200.00 and above', 'value' => '200-', 'count' => '1'],
                ]
            ]
        ];
    }
}
