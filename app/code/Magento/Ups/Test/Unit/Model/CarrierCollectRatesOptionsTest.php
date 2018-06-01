<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Test\Unit\Model;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Ups\Model\Carrier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateResultErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Xml\Security;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackResultFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as TrackingResultErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\Currency;
use Magento\Directory\Helper\Data;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\Locale\FormatInterface;
use Magento\Ups\Helper\Config;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierCollectRatesOptionsTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest;
     */
    private $rateRequest;

    /**
     * @var string;
     */
    private $allowed_methods;

    /**
     * @var int;
     */
    private $negotiatedactive;

    /**
     * @var int;
     */
    private $include_taxes;

    /**
     * set up test environment
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        
        $scopeMock = $this->getMockBuilder(ScopeConfigInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $scopeMock->expects($this->any())
           ->method('getValue')
           ->willReturnCallback([$this, 'scopeConfigGetValue']);
        $scopeMock->expects($this->any())
           ->method('isSetFlag')
           ->willReturnCallback([$this, 'scopeConfigisSetFlag']);

        $errorFactoryMock = $this->getMockBuilder(RateResultErrorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loggerInterfaceMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $securityMock = $this->getMockBuilder(Security::class)
            ->disableOriginalConstructor()
            ->getMock();

        $elementFactoryMock = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rateResultMock = $this->getMockBuilder(RateResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['getError'])
            ->getMock();

        $rateFactoryMock = $this->getMockBuilder(RateResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $rateFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($rateResultMock);

        $priceInterfaceMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $rateMethodMock = $this->getMockBuilder(Method::class)
            ->setConstructorArgs(['priceCurrency' => $priceInterfaceMock])
            ->setMethods(null)
            ->getMock();

        $methodFactoryMock = $this->getMockBuilder(MethodFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $methodFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($rateMethodMock);

        $resultFactoryMock = $this->getMockBuilder(TrackResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $trErrorFactoryMock = $this->getMockBuilder(TrackingResultErrorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statusFactoryMock = $this->getMockBuilder(StatusFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $regionFactoryMock = $this->getMockBuilder(RegionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $countryMock = $this->getMockBuilder(Country::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getData'])
            ->getMock();
            
        $countryMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
            
        $countryFactoryMock = $this->getMockBuilder(CountryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
            
        $countryFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($countryMock);

        $allowCurrencies = ['GBP'];
        $baseCurrencies = ['GBP'];
        $currencyRates = ['GBP' => ['GBP' => 1]];
        $currencyFactoryMock = $this->getMockBuilder(CurrencyFactory::class)
             ->disableOriginalConstructor()
             ->setMethods(['create'])
             ->getMock();
        $currencyMock = $this->getMockBuilder(Currency::class)
             ->disableOriginalConstructor()
             ->setMethods(['getConfigAllowCurrencies', 'getConfigBaseCurrencies', 'getCurrencyRates'])
             ->getMock();
        $currencyFactoryMock->expects($this->once())
             ->method('create')
             ->willReturn($currencyMock);
        $currencyMock->expects($this->any())
             ->method('getConfigAllowCurrencies')
             ->willReturn($allowCurrencies);
        $currencyMock->expects($this->any())
             ->method('getConfigBaseCurrencies')
             ->willReturn($baseCurrencies);
        $currencyMock->expects($this->any())
             ->method('getCurrencyRates')
             ->with($baseCurrencies, $allowCurrencies)
             ->willReturn($currencyRates);

        $dataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stockRegistryMock = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $formatInterfaceMock = $this->getMockBuilder(FormatInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configHelperMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->getMockBuilder(Carrier::class)
            ->setMethods(['_getCachedQuotes', 'canCollectRates', '_updateFreeMethodQuote', '_getBaseCurrencyRate'])
            ->setConstructorArgs(
                [
                    'scopeConfig' => $scopeMock,
                    'rateErrorFactory' => $errorFactoryMock,
                    'logger' => $loggerInterfaceMock,
                    'xmlSecurity' => $securityMock,
                    'xmlElFactory' => $elementFactoryMock,
                    'rateFactory' => $rateFactoryMock,
                    'rateMethodFactory' => $methodFactoryMock,
                    'trackFactory' => $resultFactoryMock,
                    'trackErrorFactory' => $trErrorFactoryMock,
                    'trackStatusFactory' => $statusFactoryMock,
                    'regionFactory' => $regionFactoryMock,
                    'countryFactory' => $countryFactoryMock,
                    'currencyFactory' => $currencyFactoryMock,
                    'directoryData' => $dataMock,
                    'stockRegistry' => $stockRegistryMock,
                    'localeFormat' => $formatInterfaceMock,
                    'configHelper' => $configHelperMock,
                    'httpClientFactory' => $this->createMock(\Magento\Framework\HTTP\ClientFactory::class),
                    'data' => [],
                ]
            )
            ->getMock();
        
        $this->model->expects($this->any())
             ->method('canCollectRates')
             ->willReturn(true);
             
        $this->model->expects($this->any())
             ->method('_getBaseCurrencyRate')
             ->willReturn(1.00);

        $this->rateRequest = $this->objectManager->getObject(RateRequest::class);
    }

    /**
     * Callback function, emulates getValue function
     * @param $path
     * @return null|string
     */
    public function scopeConfigGetValue($path)
    {
        $pathMap = [
            'carriers/ups/type' => 'UPS_XML',
            'carriers/ups/shipper_number' => '12345',
            'carriers/ups/allowed_methods' => $this->allowed_methods,
        ];

        return isset($pathMap[$path]) ? $pathMap[$path] : null;
    }

    /**
     * Callback function, emulates isSetFlag function
     * @param $path
     * @return bool
     */
    public function scopeConfigisSetFlag($path)
    {
        $pathMap = [
            'carriers/ups/negotiated_active' => $this->negotiatedactive,
            'carriers/ups/include_taxes' => $this->include_taxes,
        ];
       
        if (isset($pathMap[$path])) {
            if ($pathMap[$path]) {
                 return(true);
            }
        }
        return(false);
    }
    
    /**
     * @param int $neg
     * @param int $tax
     * @param string $file
     * @param string $method
     * @param float $expectedprice
     * @dataProvider collectRatesDataProvider
     */
    public function testCollectRates($neg, $tax, $file, $method, $expectedprice)
    {
        $this->negotiatedactive = $neg;
        $this->include_taxes = $tax;
        $this->allowed_methods = $method;

        $response = file_get_contents(__DIR__ . $file);
        $this->model->expects($this->any())
             ->method('_getCachedQuotes')
             ->willReturn($response);
  
        $rates = $this->model->collectRates($this->rateRequest)->getAllRates();
        $this->assertEquals($expectedprice, $rates[0]->getData('cost'));
        $this->assertEquals($method, $rates[0]->getData('method'));
    }

    /**
     * Get list of rates variations
     * @return array
     */
    public function collectRatesDataProvider()
    {
        return [
            [0, 0, '/_files/ups_rates_response_option1.xml', '11', 6.45 ],
            [0, 0, '/_files/ups_rates_response_option2.xml', '65', 29.59 ],
            [0, 1, '/_files/ups_rates_response_option3.xml', '11', 7.74 ],
            [0, 1, '/_files/ups_rates_response_option4.xml', '65', 29.59 ],
            [1, 0, '/_files/ups_rates_response_option5.xml', '11', 9.35 ],
            [1, 0, '/_files/ups_rates_response_option6.xml', '65', 41.61 ],
            [1, 1, '/_files/ups_rates_response_option7.xml', '11', 11.22 ],
            [1, 1, '/_files/ups_rates_response_option8.xml', '65', 41.61 ],
        ];
    }
}
