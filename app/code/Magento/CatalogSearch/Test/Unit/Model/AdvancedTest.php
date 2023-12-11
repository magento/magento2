<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogSearch\Model\ResourceModel\Advanced;
use Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection;
use Magento\CatalogSearch\Model\ResourceModel\AdvancedFactory;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see \Magento\CatalogSearch\Model\Advanced
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedTest extends TestCase
{
    /**
     * @var MockObject|Collection
     */
    protected $collection;

    /**
     * @var MockObject|Advanced
     */
    protected $resource;

    /**
     * @var MockObject[]|Attribute[]
     */
    protected $attributes;

    /**
     * @var MockObject|\Magento\Framework\Data\Collection
     */
    protected $dataCollection;

    /**
     * @var MockObject|Currency
     */
    private $currency;

    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MockObject|Store
     */
    private $store;

    protected function setUp(): void
    {
        $this->collection = $this->createPartialMock(
            Collection::class,
            [
                'addAttributeToSelect',
                'setStore',
                'addMinimalPrice',
                'addTaxPercents',
                'addStoreFilter',
                'setVisibility',
                'addFieldsToFilter'
            ]
        );
        $this->resource = $this->createPartialMock(
            Advanced::class,
            ['prepareCondition', '__wakeup', 'getIdFieldName']
        );

        $this->dataCollection = $this->createPartialMock(\Magento\Framework\Data\Collection::class, ['getIterator']);

        $this->currency = $this->getMockBuilder(Currency::class)
            ->setMethods(['getRate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->getMockBuilder(Store::class)
            ->setMethods(['getCurrentCurrencyCode', 'getBaseCurrencyCode', 'getBaseCurrency'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->store->expects($this->any())
            ->method('getBaseCurrency')
            ->willReturn($this->currency);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
    }

    /**
     * @return array
     */
    public function addFiltersDataProvider()
    {
        return array_merge(
            [
                'sku' => [
                    'attributes' => [
                        $this->createAttribute(
                            $this->createBackend('catalog_product_entity'),
                            $this->createSource(),
                            'sku',
                            'SKU',
                            'text',
                            'static'
                        )
                    ],
                    'values' => ['sku' => 'simple']
                ],
                'color_multiselect' => [
                    'attributes' => [
                        $this->createAttribute(
                            $this->createBackend('color_multiselect'),
                            $this->createSource(['label' => 'Color']),
                            'color',
                            'Color',
                            'multiselect',
                            'static'
                        )
                    ],
                    'values' => ['color' => [100 => 'red', 200 => 'blue']],
                    'currentCurrencyCode' => 'GBP',
                    'baseCurrencyCode' => 'USD'
                ],
                'color_select' => [
                    'attributes' => [
                        $this->createAttribute(
                            $this->createBackend('color_select'),
                            $this->createSource(['label' => 'Color']),
                            'color',
                            'Color',
                            'select',
                            'static'
                        )
                    ],
                    'values' => ['color' => 'red'],
                    'currentCurrencyCode' => 'GBP',
                    'baseCurrencyCode' => 'USD'
                ],
                'boolean' => [
                    'attributes' => [
                        $this->createAttribute(
                            $this->createBackend('boolean'),
                            $this->createSource(['label' => 'Color']),
                            'is_active',
                            'Is active?',
                            'boolean',
                            'static'
                        )
                    ],
                    'values' => ['is_active' => 0],
                    'currentCurrencyCode' => 'GBP',
                    'baseCurrencyCode' => 'USD'
                ],
            ],
            $this->addFiltersPriceDataProvider()
        );
    }

    /**
     * @param array $attributes
     * @param array $values
     * @param string $currentCurrencyCode
     * @param string $baseCurrencyCode
     * @dataProvider addFiltersDataProvider
     */
    public function testAddFiltersVerifyAddConditionsToRegistry(
        array $attributes,
        array $values,
        $currentCurrencyCode = 'GBP',
        $baseCurrencyCode = 'USD'
    ) {
        $registry = new Registry();

        $this->collection->expects($this->any())->method('addAttributeToSelect')->willReturnSelf();
        $this->collection->expects($this->any())->method('setStore')->willReturnSelf();
        $this->collection->expects($this->any())->method('addMinimalPrice')->willReturnSelf();
        $this->collection->expects($this->any())->method('addTaxPercents')->willReturnSelf();
        $this->collection->expects($this->any())->method('addStoreFilter')->willReturnSelf();
        $this->collection->expects($this->any())->method('setVisibility')->willReturnSelf();
        $this->resource->expects($this->any())->method('prepareCondition')
            ->willReturn(['like' => '%simple%']);
        $this->resource->expects($this->any())->method('getIdFieldName')->willReturn('entity_id');
        $this->dataCollection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator($attributes));
        $objectManager = new ObjectManager($this);

        $advancedFactory = $this->getMockBuilder(AdvancedFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $advancedFactory->expects($this->once())->method('create')->willReturn($this->resource);

        $productCollectionFactory =
            $this->getMockBuilder(CollectionFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $productCollectionFactory->expects($this->any())->method('create')->willReturn($this->collection);

        $this->store->expects($this->any())
            ->method('getCurrentCurrencyCode')
            ->willReturn($currentCurrencyCode);
        $this->store->expects($this->any())
            ->method('getBaseCurrencyCode')
            ->willReturn($baseCurrencyCode);
        $this->currency->expects($this->any())
            ->method('getRate')
            ->with($currentCurrencyCode)
            ->willReturn(1.5);

        $currency = $this->getMockBuilder(Currency::class)
            ->setMethods(['load', 'format'])
            ->disableOriginalConstructor()
            ->getMock();
        $currency->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $currency->expects($this->any())
            ->method('format')
            ->willReturnArgument(0);
        $currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $currencyFactory->expects($this->any())
            ->method('create')
            ->willReturn($currency);

        /** @var \Magento\CatalogSearch\Model\Advanced $instance */
        $instance = $objectManager->getObject(
            \Magento\CatalogSearch\Model\Advanced::class,
            [
                'registry' => $registry,
                'data' => ['attributes' => $this->dataCollection],
                'advancedFactory' => $advancedFactory,
                'productCollectionFactory' => $productCollectionFactory,
                'storeManager' => $this->storeManager,
                'currencyFactory' => $currencyFactory,
                'collectionProvider' => null
            ]
        );
        $instance->addFilters($values);
        $this->assertNotNull($registry->registry('advanced_search_conditions'));
    }

    /**
     * @param $table
     * @return MockObject|AbstractBackend
     */
    private function createBackend($table)
    {
        $backend = $this->createPartialMock(
            AbstractBackend::class,
            ['getTable']
        );
        $backend->expects($this->once())
            ->method('getTable')
            ->willReturn($table);
        return $backend;
    }

    /**
     * @param string $optionText
     * @return MockObject
     */
    private function createSource($optionText = 'optionText')
    {
        $source = $this->getMockBuilder(AbstractSource::class)
            ->setMethods(['getOptionText'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $source->expects($this->any())
            ->method('getOptionText')
            ->willReturn($optionText);
        return $source;
    }

    /**
     * @return array
     */
    private function addFiltersPriceDataProvider()
    {
        return [
            'price_without_currency' => [
                'attributes' => [
                    $this->createAttribute(
                        $this->createBackend('table_price_without_currency'),
                        $this->createSource(),
                        'price',
                        'Price',
                        'multiselect',
                        'static'
                    )
                ],
                'values' => ['price' => ['from' => 10, 'to' => 40]],
                'currentCurrencyCode' => 'GBP',
                'baseCurrencyCode' => 'USD'
            ],
            'price_without_to' => [
                'attributes' => [
                    $this->createAttribute(
                        $this->createBackend('price_without_to'),
                        $this->createSource(),
                        'price',
                        'Price',
                        'multiselect',
                        'static'
                    )
                ],
                'values' => ['price' => ['from' => 10, 'to' => '']],
                'currentCurrencyCode' => 'GBP',
                'baseCurrencyCode' => 'USD'
            ],
            'price_without_from' => [
                'attributes' => [
                    $this->createAttribute(
                        $this->createBackend('price_without_from'),
                        $this->createSource(),
                        'price',
                        'Price',
                        'multiselect',
                        'static'
                    )
                ],
                'values' => ['price' => ['from' => '', 'to' => 30]],
                'currentCurrencyCode' => 'GBP',
                'baseCurrencyCode' => 'USD'
            ],
            'price_empty' => [
                'attributes' => [
                    $this->createAttribute(
                        $this->createBackend('price_empty'),
                        $this->createSource(),
                        'price',
                        'Price',
                        'multiselect',
                        'static'
                    )
                ],
                'values' => ['price' => ['from' => '', 'to' => '']],
                'currentCurrencyCode' => 'GBP',
                'baseCurrencyCode' => 'USD'
            ],
            'price_with_currency' => [
                'attributes' => [
                    $this->createAttribute(
                        $this->createBackend('price_with_currency'),
                        $this->createSource(),
                        'price',
                        'Price',
                        'multiselect',
                        'static'
                    )
                ],
                'values' => ['price' => ['from' => 10, 'to' => 40, 'currency' => 'ASD']],
                'currentCurrencyCode' => 'GBP',
                'baseCurrencyCode' => 'USD'
            ]
        ];
    }

    /**
     * @param $backend
     * @param null $source
     * @param null $attributeCode
     * @param null $storeLabel
     * @param null $frontendInput
     * @param null $backendType
     * @return Attribute|MockObject
     */
    private function createAttribute(
        $backend,
        $source = null,
        $attributeCode = null,
        $storeLabel = null,
        $frontendInput = null,
        $backendType = null
    ) {
        $attribute = $this->createPartialMock(Attribute::class, [
            'getAttributeCode',
            'getStoreLabel',
            'getFrontendInput',
            'getBackend',
            'getBackendType',
            'getSource',
            '__wakeup'
        ]);
        $attribute->expects($this->any())->method('getBackend')->willReturn($backend);
        $attribute->expects($this->any())->method('getSource')->willReturn($source);
        $attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $attribute->expects($this->any())->method('getStoreLabel')->willReturn($storeLabel);
        $attribute->expects($this->any())->method('getFrontendInput')->willReturn($frontendInput);
        $attribute->expects($this->any())->method('getBackendType')->willReturn($backendType);
        return $attribute;
    }
}
