<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model;

/**
 * Class AdvancedTest
 * @see \Magento\CatalogSearch\Model\Advanced
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
     */
    protected $collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogSearch\Model\ResourceModel\Advanced
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogSearch\Model\ResourceModel\ResourceProvider
     */
    protected $resourceProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]|\Magento\Catalog\Model\ResourceModel\Eav\Attribute[]
     */
    protected $attributes;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Collection
     */
    protected $dataCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Directory\Model\Currency
     */
    private $currency;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    private $store;

    protected function setUp()
    {
        $this->collection = $this->createPartialMock(
            \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection::class,
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
            \Magento\CatalogSearch\Model\ResourceModel\Advanced::class,
            ['prepareCondition', '__wakeup', 'getIdFieldName']
        );

        $this->resourceProvider = $this->getMockBuilder(
            \Magento\CatalogSearch\Model\ResourceModel\ResourceProvider::class
        )
            ->setMethods(['getResource', 'getResourceCollection', 'getAdvancedResultCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataCollection = $this->createPartialMock(\Magento\Framework\Data\Collection::class, ['getIterator']);

        $this->currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->setMethods(['getRate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getCurrentCurrencyCode', 'getBaseCurrencyCode', 'getBaseCurrency'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->store->expects($this->any())
            ->method('getBaseCurrency')
            ->willReturn($this->currency);
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
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
        $registry = new \Magento\Framework\Registry();

        $this->collection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('setStore')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('addMinimalPrice')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('addTaxPercents')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('addStoreFilter')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('setVisibility')->will($this->returnSelf());
        $this->resource->expects($this->any())->method('prepareCondition')
            ->will($this->returnValue(['like' => '%simple%']));
        $this->resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('entity_id'));
        $this->dataCollection->expects($this->any())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($attributes)));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $advancedFactory = $this->getMockBuilder(\Magento\CatalogSearch\Model\ResourceModel\AdvancedFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $advancedFactory->expects($this->once())->method('create')->willReturn($this->resource);

        $productCollectionFactory =
            $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
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

        $currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->setMethods(['load', 'format'])
            ->disableOriginalConstructor()
            ->getMock();
        $currency->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $currency->expects($this->any())
            ->method('format')
            ->willReturnArgument(0);
        $currencyFactory = $this->getMockBuilder(\Magento\Directory\Model\CurrencyFactory::class)
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
                'resourceProvider' => $this->resourceProvider,
                'data' => ['attributes' => $this->dataCollection],
                'advancedFactory' => $advancedFactory,
                'productCollectionFactory' => $productCollectionFactory,
                'storeManager' => $this->storeManager,
                'currencyFactory' => $currencyFactory,
            ]
        );
        $instance->addFilters($values);
        $this->assertNotNull($registry->registry('advanced_search_conditions'));
    }

    /**
     * @param $table
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    private function createBackend($table)
    {
        $backend = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class,
            ['getTable']
        );
        $backend->expects($this->once())
            ->method('getTable')
            ->willReturn($table);
        return $backend;
    }

    /**
     * @param string $optionText
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createSource($optionText = 'optionText')
    {
        $source = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class)
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
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttribute(
        $backend,
        $source = null,
        $attributeCode = null,
        $storeLabel = null,
        $frontendInput = null,
        $backendType = null
    ) {
        $attribute = $this->createPartialMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [
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
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        $attribute->expects($this->any())->method('getStoreLabel')->will($this->returnValue($storeLabel));
        $attribute->expects($this->any())->method('getFrontendInput')->will($this->returnValue($frontendInput));
        $attribute->expects($this->any())->method('getBackendType')->will($this->returnValue($backendType));
        return $attribute;
    }
}
