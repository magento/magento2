<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Price;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\Dynamic\Auto;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Filter\Price as FilterPrice;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Price as PriceResource;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\State;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\Price
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var DataBuilder|MockObject
     */
    private $itemDataBuilder;

    /**
     * @var PriceResource|MockObject
     */
    private $resource;

    /**
     * @var Auto|MockObject
     */
    private $algorithm;

    /**
     * @var Layer|MockObject
     */
    private $layer;

    /**
     * @var Price|MockObject
     */
    private $dataProvider;

    /**
     * @var FilterPrice
     */
    private $target;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var  ItemFactory|MockObject
     */
    private $filterItemFactory;

    /**
     * @var  State|MockObject
     */
    private $state;

    /**
     * @var  Attribute|MockObject
     */
    private $attribute;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);

        $dataProviderFactory = $this->createPartialMock(PriceFactory::class, ['create']);

        $this->resource = $this->createPartialMock(
            PriceResource::class,
            ['applyPriceRange']
        );

        $priceData = [];
        $this->dataProvider = $this->createPartialMockWithReflection(
            Price::class,
            ['setResource', 'getResource']
        );
        $dataProvider = $this->dataProvider;
        $this->dataProvider->method('setResource')->willReturnCallback(
            function ($res) use (&$priceData, $dataProvider) {
                $priceData['resource'] = $res;
                return $dataProvider;
            }
        );
        $this->dataProvider->method('getResource')->willReturnCallback(function () use (&$priceData) {
            return $priceData['resource'] ?? null;
        });
        $this->dataProvider->setResource($this->resource);

        $dataProviderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->dataProvider);

        $this->layer = $this->createPartialMock(Layer::class, ['getState']);

        $this->state = $this->createPartialMock(State::class, ['addFilter']);
        $this->layer->method('getState')->willReturn($this->state);

        $this->itemDataBuilder = $this->createPartialMock(DataBuilder::class, ['addItemData', 'build']);

        $this->filterItemFactory = $this->createPartialMock(ItemFactory::class, ['create']);

        $filterItem = $this->createPartialMockWithReflection(
            Item::class,
            ['setFilter', 'setData', 'setCount', 'setLabel', 'setValueString']
        );
        $filterItem->method('setFilter')->willReturnSelf();
        $filterItem->method('setData')->willReturnSelf();
        $filterItem->method('setCount')->willReturnSelf();
        $filterItem->method('setLabel')->willReturnSelf();
        $filterItem->method('setValueString')->willReturnSelf();
        $this->filterItemFactory->method('create')->willReturn($filterItem);

        $escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);
        $escaper->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $attributeData = [];
        $this->attribute = $this->createPartialMockWithReflection(
            Attribute::class,
            ['setAttributeCode', 'getAttributeCode', 'getFrontend', 'getBackend', 'getSource',
             'getDefaultFrontendLabel', 'getStoreLabel', 'getIsRequired', '_construct']
        );
        $attribute = $this->attribute;
        $this->attribute->method('setAttributeCode')->willReturnCallback(
            function ($code) use (&$attributeData, $attribute) {
                $attributeData['attribute_code'] = $code;
                return $attribute;
            }
        );
        $this->attribute->method('getAttributeCode')->willReturnCallback(function () use (&$attributeData) {
            return $attributeData['attribute_code'] ?? null;
        });
        $this->attribute->method('getFrontend')->willReturn(null);
        $this->attribute->method('getBackend')->willReturn(null);
        $this->attribute->method('getSource')->willReturn(null);
        $this->attribute->method('getDefaultFrontendLabel')->willReturn(null);
        $this->attribute->method('getStoreLabel')->willReturn(null);
        $this->attribute->method('getIsRequired')->willReturn(false);
        $this->attribute->method('_construct')->willReturn(null);

        $algorithmFactory = $this->createPartialMock(AlgorithmFactory::class, ['create']);

        $this->algorithm = $this->createPartialMock(Auto::class, ['getItemsData']);

        $algorithmFactory->method('create')->willReturn($this->algorithm);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            FilterPrice::class,
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
     *
     * @return void
     */
    #[DataProvider('applyWithEmptyRequestDataProvider')]
    public function testApplyWithEmptyRequest($requestValue, $idValue): void
    {
        $requestField = 'test_request_var';
        $idField = 'id';

        $this->target->setRequestVar($requestField);

        $this->request->method('getParam')->willReturnMap([
            [$requestField, null, $requestValue],
            [$idField, null, $idValue],
        ]);

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

    /**
     * @return void
     */
    public function testApply(): void
    {
        $priceId = '15-50';
        $requestVar = 'test_request_var';

        $this->target->setRequestVar($requestVar);
        $this->request->expects($this->exactly(1))
            ->method('getParam')
            ->willReturnCallback(
                function ($field) use ($requestVar, $priceId) {
                    $this->assertContains($field, [$requestVar, 'id']);
                    return $priceId;
                }
            );

        $this->target->apply($this->request);
    }

    /**
     * @return void
     */
    public function testGetItems(): void
    {
        $this->target->setAttributeModel($this->attribute);
        $attributeCode = 'attributeCode';
        $this->attribute->setAttributeCode($attributeCode);
        $this->algorithm->method('getItemsData')->willReturn([]);
        $this->target->getItems();
    }
}
