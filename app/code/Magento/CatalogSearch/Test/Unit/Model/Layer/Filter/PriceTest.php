<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Price;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\State;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogSearch\Model\Layer\Filter\Price
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends TestCase
{
    /**
     * @var Attribute|MockObject
     */
    private $attribute;

    /**
     * @var DataBuilder|MockObject
     */
    private $itemDataBuilder;

    /**
     * @var Collection|MockObject
     */
    private $fulltextCollection;

    /**
     * @var Layer|MockObject
     */
    private $layer;

    /**
     * @var Price|MockObject
     */
    private $dataProvider;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Filter\Price
     */
    private $target;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ItemFactory|MockObject
     */
    private $filterItemFactory;

    /**
     * @var State|MockObject
     */
    private $state;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParam'])
            ->getMockForAbstractClass();

        $dataProviderFactory = $this->getMockBuilder(PriceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])->getMock();

        $this->dataProvider = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->addMethods(['setPriceId', 'getPrice'])
            ->getMock();

        $dataProviderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->dataProvider);

        $this->layer = $this->getMockBuilder(Layer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getState', 'getProductCollection'])
            ->getMock();

        $this->state = new State();
        $this->layer->expects($this->any())
            ->method('getState')
            ->willReturn($this->state);

        $this->fulltextCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFieldToFilter', 'getFacetedData'])
            ->getMock();

        $this->layer->expects($this->any())
            ->method('getProductCollection')
            ->willReturn($this->fulltextCollection);

        $this->itemDataBuilder = $this->getMockBuilder(DataBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addItemData', 'build'])
            ->getMock();

        $this->filterItemFactory = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

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
        $priceFormatter = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $priceFormatter->method('format')
            ->willReturnCallback(
                function ($number) {
                    return sprintf('$%01.2f', $number);
                }
            );

        $escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['escapeHtml'])
            ->getMock();
        $escaper->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributeCode', 'getFrontend'])
            ->addMethods(['getIsFilterable'])
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
                'priceCurrency' => $priceFormatter,
            ]
        );
    }

    /**
     * @param int|null $requestValue
     * @param int|bool|null $idValue
     *
     * @return void
     * @dataProvider applyWithEmptyRequestDataProvider
     */
    public function testApplyWithEmptyRequest(?int $requestValue, $idValue): void
    {
        $requestField = 'test_request_var';
        $idField = 'id';

        $this->target->setRequestVar($requestField);

        $this->request
            ->method('getParam')
            ->with($requestField)
            ->willReturnMap(
                [
                    [$requestField, $requestValue],
                    [$idField, $idValue],
                ]
            );

        $result = $this->target->apply($this->request);
        $this->assertSame($this->target, $result);
    }

    /**
     * @return array
     */
    public function applyWithEmptyRequestDataProvider(): array
    {
        return [
            [
                'requestValue' => null,
                'id' => 0
            ],
            [
                'requestValue' => 0,
                'id' => false
            ],
            [
                'requestValue' => 0,
                'id' => null
            ]
        ];
    }

    /**
     * @dataProvider applyDataProvider
     */
    public function testApply(string $filter, array $expected): void
    {
        $requestVar = 'price';
        $this->request->expects($this->exactly(1))
            ->method('getParam')
            ->with($requestVar)
            ->willReturn($filter);

        $this->fulltextCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('price')->willReturnSelf();

        $this->target->setCurrencyRate(1);
        $this->target->apply($this->request);
        $actual = [];
        foreach ($this->state->getFilters() as $item) {
            $actual[] = ['label' => $item->getLabel(), 'value' => $item->getValue(), 'count' => $item->getCount()];
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function applyDataProvider(): array
    {
        return [
            [
                '10-50',
                [
                    ['label' => '$10.00 - $49.99', 'value' => ['10', '50'], 'count' => '0'],
                ]
            ],
            [
                '-50',
                [
                    ['label' => '$0.00 - $49.99', 'value' => ['', '50'], 'count' => '0'],
                ]
            ],
            [
                '10-',
                [
                    ['label' => '$10.00 and above', 'value' => ['10', ''], 'count' => '0'],
                ]
            ]
        ];
    }

    /**
     * @return void
     */
    public function testGetItems(): void
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
