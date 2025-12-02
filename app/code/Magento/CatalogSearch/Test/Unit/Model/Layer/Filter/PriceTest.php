<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogSearch\Model\Layer\Filter\Price
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends TestCase
{
    use MockCreationTrait;
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
        $this->request = $this->createMock(RequestInterface::class);

        $dataProviderFactory = $this->createPartialMock(
            PriceFactory::class,
            ['create']
        );

        $this->dataProvider = $this->createPartialMockWithReflection(
            Price::class,
            ['setPriceId', 'getPrice']
        );

        $dataProviderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->dataProvider);

        $this->layer = $this->createPartialMock(
            Layer::class,
            ['getState', 'getProductCollection']
        );

        $this->state = new State();
        $this->layer->method('getState')
            ->willReturn($this->state);

        $this->fulltextCollection = $this->createPartialMock(
            Collection::class,
            ['addFieldToFilter', 'getFacetedData']
        );

        $this->layer->method('getProductCollection')
            ->willReturn($this->fulltextCollection);

        $this->itemDataBuilder = $this->createPartialMock(
            DataBuilder::class,
            ['addItemData', 'build']
        );

        $this->filterItemFactory = $this->createPartialMock(
            ItemFactory::class,
            ['create']
        );

        $this->filterItemFactory->method('create')
            ->willReturnCallback(
                function (array $data) {
                    return new Item(
                        $this->createMock(UrlInterface::class),
                        $this->createMock(Pager::class),
                        $data
                    );
                }
            );
        $priceFormatter = $this->createMock(PriceCurrencyInterface::class);
        $priceFormatter->method('format')
            ->willReturnCallback(
                function ($number) {
                    return sprintf('$%01.2f', $number);
                }
            );

        $escaper = $this->createPartialMock(
            Escaper::class,
            ['escapeHtml']
        );
        $escaper->method('escapeHtml')
            ->willReturnArgument(0);

        $this->attribute = $this->createPartialMockWithReflection(
            Attribute::class,
            ['getAttributeCode', 'getFrontend', 'getIsFilterable']
        );

        $objectManagerHelper = new ObjectManager($this);
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
     */
    #[DataProvider('applyWithEmptyRequestDataProvider')]
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

    #[DataProvider('applyDataProvider')]
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
    public static function applyDataProvider(): array
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
        $this->attribute->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->with($attributeCode)
            ->willReturn([]);
        $this->target->getItems();
    }
}
