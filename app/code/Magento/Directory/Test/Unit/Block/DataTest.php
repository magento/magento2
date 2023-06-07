<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Block;

use Magento\Directory\Block\Data;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Escaper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /** @var  Data */
    private $block;

    /** @var  Context|MockObject */
    private $contextMock;

    /** @var  HelperData|MockObject */
    private $helperDataMock;

    /** @var  Config|MockObject */
    private $cacheTypeConfigMock;

    /** @var  CountryCollectionFactory|MockObject */
    private $countryCollectionFactoryMock;

    /** @var  ScopeConfigInterface|MockObject */
    private $scopeConfigMock;

    /** @var  StoreManagerInterface|MockObject */
    private $storeManagerMock;

    /** @var  Store|MockObject */
    private $storeMock;

    /** @var  CountryCollection|MockObject */
    private $countryCollectionMock;

    /** @var  LayoutInterface|MockObject */
    private $layoutMock;

    /** @var SerializerInterface|MockObject */
    private $serializerMock;

    /** @var \Magento\Framework\Escaper */
    private $escaper;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtmlAttr'])
            ->getMock();
        $this->prepareContext();

        $this->helperDataMock = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheTypeConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareCountryCollection();

        $this->block = $objectManagerHelper->getObject(
            Data::class,
            [
                'context' => $this->contextMock,
                'directoryHelper' => $this->helperDataMock,
                'configCacheType' => $this->cacheTypeConfigMock,
                'countryCollectionFactory' => $this->countryCollectionFactoryMock
            ]
        );

        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->block,
            'serializer',
            $this->serializerMock
        );
    }

    protected function prepareContext()
    {
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->contextMock->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);

        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);

        $this->contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($this->escaper);
    }

    protected function prepareCountryCollection()
    {
        $this->countryCollectionMock = $this->getMockBuilder(
            \Magento\Directory\Model\ResourceModel\Country\Collection::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->countryCollectionFactoryMock = $this->getMockBuilder(
            \Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'create'
                ]
            )
            ->getMock();

        $this->countryCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->countryCollectionMock);
    }

    /**
     * @param string $storeCode
     * @param int $defaultCountry
     * @param string $destinations
     * @param array $expectedDestinations
     * @param array $options
     * @param string $resultHtml
     * @dataProvider dataProviderGetCountryHtmlSelect
     */
    public function testGetCountryHtmlSelect(
        $storeCode,
        $defaultCountry,
        $destinations,
        $expectedDestinations,
        $options,
        $resultHtml
    ) {
        $this->helperDataMock->expects($this->once())
            ->method('getDefaultCountry')
            ->willReturn($defaultCountry);

        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn($storeCode);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn('serializedData');

        $this->cacheTypeConfigMock->expects($this->once())
            ->method('load')
            ->with('DIRECTORY_COUNTRY_SELECT_STORE_' . $storeCode)
            ->willReturn(false);
        $this->cacheTypeConfigMock->expects($this->once())
            ->method('save')
            ->with('serializedData', 'DIRECTORY_COUNTRY_SELECT_STORE_' . $storeCode)
            ->willReturnSelf();

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('general/country/destinations', ScopeInterface::SCOPE_STORE)
            ->willReturn($destinations);

        $this->countryCollectionMock->expects($this->once())
            ->method('loadByStore')
            ->willReturnSelf();
        $this->countryCollectionMock->expects($this->any())
            ->method('setForegroundCountries')
            ->with($expectedDestinations)
            ->willReturnSelf();
        $this->countryCollectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);

        $elementHtmlSelect = $this->mockElementHtmlSelect($defaultCountry, $options, $resultHtml);

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->willReturn($elementHtmlSelect);

        $this->assertEquals($resultHtml, $this->block->getCountryHtmlSelect());
    }

    /**
     * 1. Store code
     * 2. Default Country ID
     * 3. Top Destinations
     * 4. Exploded Top Destinations
     * 5. Result options
     *
     * @return array
     */
    public function dataProviderGetCountryHtmlSelect()
    {
        return [
            [
                'default',
                1,
                '',
                [],
                [
                    [
                        'value' => 'US',
                        'label' => 'United States',
                    ],
                ],
                'result html',
            ],
            [
                'default',
                1,
                'US',
                [
                    0 => 'US',
                ],
                [
                    [
                        'value' => 'US',
                        'label' => 'United States',
                    ],
                ],
                'result html',
            ],
            [
                'default',
                1,
                'US,GB',
                [
                    0 => 'US',
                    1 => 'GB',
                ],
                [
                    [
                        'value' => 'US',
                        'label' => 'United States',
                    ],
                    [
                        'value' => 'GB',
                        'label' => 'Great Britain',
                    ],
                ],
                'result html',
            ],
        ];
    }

    /**
     * @param $defaultCountry
     * @param $options
     * @param $resultHtml
     * @return MockObject
     */
    protected function mockElementHtmlSelect($defaultCountry, $options, $resultHtml)
    {
        $name = 'country_id';
        $id = 'country';
        $title = 'Country';

        $elementHtmlSelect = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setName',
                    'setId',
                    'setTitle',
                    'setValue',
                    'setOptions',
                    'setExtraParams',
                    'getHtml',
                ]
            )
            ->getMock();

        $elementHtmlSelect->expects($this->once())
            ->method('setName')
            ->with($name)
            ->willReturnSelf();
        $elementHtmlSelect->expects($this->once())
            ->method('setId')
            ->with($id)
            ->willReturnSelf();
        $elementHtmlSelect->expects($this->once())
            ->method('setTitle')
            ->with(__($title))
            ->willReturnSelf();
        $elementHtmlSelect->expects($this->once())
            ->method('setValue')
            ->with($defaultCountry)
            ->willReturnSelf();
        $elementHtmlSelect->expects($this->once())
            ->method('setOptions')
            ->with($options)
            ->willReturnSelf();
        $elementHtmlSelect->expects($this->once())
            ->method('setExtraParams')
            ->with('data-validate="{\'validate-select\':true}"')
            ->willReturnSelf();
        $elementHtmlSelect->expects($this->once())
            ->method('getHtml')
            ->willReturn($resultHtml);
        $this->escaper->expects($this->once())
            ->method('escapeHtmlAttr')
            ->with(__($title))
            ->willReturn(__($title));

        return $elementHtmlSelect;
    }
}
