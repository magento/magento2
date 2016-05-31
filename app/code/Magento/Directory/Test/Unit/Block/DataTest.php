<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Block;

use Magento\Directory\Block\Data;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Data */
    protected $model;

    /** @var  Context |\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var  HelperData |\PHPUnit_Framework_MockObject_MockObject */
    protected $helperData;

    /** @var  EncoderInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonEncoder;

    /** @var  Config |\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheTypeConfig;

    /** @var  RegionCollectionFactory |\PHPUnit_Framework_MockObject_MockObject */
    protected $regionCollectionFactory;

    /** @var  CountryCollectionFactory |\PHPUnit_Framework_MockObject_MockObject */
    protected $countryCollectionFactory;

    /** @var  ScopeConfigInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var  StoreManagerInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var  Store |\PHPUnit_Framework_MockObject_MockObject */
    protected $store;

    /** @var  CountryCollection |\PHPUnit_Framework_MockObject_MockObject */
    protected $countryCollection;

    /** @var  LayoutInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $layout;

    protected function setUp()
    {
        $this->prepareContext();

        $this->helperData = $this->getMockBuilder('Magento\Directory\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonEncoder = $this->getMockBuilder('Magento\Framework\Json\EncoderInterface')
            ->getMockForAbstractClass();

        $this->cacheTypeConfig = $this->getMockBuilder('Magento\Framework\App\Cache\Type\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->regionCollectionFactory = $this->getMockBuilder(
            'Magento\Directory\Model\ResourceModel\Region\CollectionFactory'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareCountryCollection();

        $this->model = new Data(
            $this->context,
            $this->helperData,
            $this->jsonEncoder,
            $this->cacheTypeConfig,
            $this->regionCollectionFactory,
            $this->countryCollectionFactory
        );
    }

    protected function prepareContext()
    {
        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->getMockForAbstractClass();

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $this->layout = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);

        $this->context->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($this->storeManager);

        $this->context->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);
    }

    protected function prepareCountryCollection()
    {
        $this->countryCollection = $this->getMockBuilder('Magento\Directory\Model\ResourceModel\Country\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->countryCollectionFactory = $this->getMockBuilder(
            'Magento\Directory\Model\ResourceModel\Country\CollectionFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods([
                'create'
            ])
            ->getMock();

        $this->countryCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->countryCollection);
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
        $this->helperData->expects($this->once())
            ->method('getDefaultCountry')
            ->willReturn($defaultCountry);

        $this->store->expects($this->once())
            ->method('getCode')
            ->willReturn($storeCode);

        $this->cacheTypeConfig->expects($this->once())
            ->method('load')
            ->with('DIRECTORY_COUNTRY_SELECT_STORE_' . $storeCode)
            ->willReturn(false);
        $this->cacheTypeConfig->expects($this->once())
            ->method('save')
            ->with(serialize($options), 'DIRECTORY_COUNTRY_SELECT_STORE_' . $storeCode)
            ->willReturnSelf();

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('general/country/destinations', ScopeInterface::SCOPE_STORE)
            ->willReturn($destinations);

        $this->countryCollection->expects($this->once())
            ->method('loadByStore')
            ->willReturnSelf();
        $this->countryCollection->expects($this->any())
            ->method('setForegroundCountries')
            ->with($expectedDestinations)
            ->willReturnSelf();
        $this->countryCollection->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);

        $elementHtmlSelect = $this->mockElementHtmlSelect($defaultCountry, $options, $resultHtml);

        $this->layout->expects($this->once())
            ->method('createBlock')
            ->willReturn($elementHtmlSelect);

        $this->assertEquals($resultHtml, $this->model->getCountryHtmlSelect());
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockElementHtmlSelect($defaultCountry, $options, $resultHtml)
    {
        $name = 'country_id';
        $id = 'country';
        $title = 'Country';

        $elementHtmlSelect = $this->getMockBuilder('Magento\Framework\View\Element\Html\Select')
            ->disableOriginalConstructor()
            ->setMethods([
                'setName',
                'setId',
                'setTitle',
                'setValue',
                'setOptions',
                'setExtraParams',
                'getHtml',
            ])
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

        return $elementHtmlSelect;
    }
}
