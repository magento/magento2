<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Usps\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Helper\Carrier as CarrierHelper;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Shipment\ReturnShipment;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Usps\Helper\Data as DataHelper;
use Magento\Usps\Model\Carrier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends TestCase
{
    /**
     * @var \Zend_Http_Response|MockObject
     */
    private $httpResponse;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Error|MockObject
     */
    private $error;

    /**
     * @var ErrorFactory|MockObject
     */
    private $errorFactory;

    /**
     * @var Carrier|MockObject
     */
    private $carrier;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scope;

    /**
     * @var DataHelper|MockObject
     */
    private $dataHelper;

    /**
     * @var ZendClient|MockObject
     */
    private $httpClient;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->scope = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scope->method('getValue')
            ->willReturnCallback([$this, 'scopeConfiggetValue']);

        $xmlElFactory = $this->getXmlFactory();
        $rateFactory = $this->getRateFactory();
        $rateMethodFactory = $this->getRateMethodFactory();
        $httpClientFactory = $this->getHttpClientFactory();

        $data = ['id' => 'usps', 'store' => '1'];

        $this->error = $this->getMockBuilder(Error::class)
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();

        $this->errorFactory = $this->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->errorFactory->expects($this->any())->method('create')->willReturn($this->error);

        $carrierHelper = $this->getCarrierHelper();
        $productCollectionFactory = $this->getProductCollectionFactory();

        $arguments = [
            'scopeConfig' => $this->scope,
            'xmlSecurity' => new Security(),
            'xmlElFactory' => $xmlElFactory,
            'rateFactory' => $rateFactory,
            'rateMethodFactory' => $rateMethodFactory,
            'httpClientFactory' => $httpClientFactory,
            'data' => $data,
            'rateErrorFactory' => $this->errorFactory,
            'carrierHelper' => $carrierHelper,
            'productCollectionFactory' => $productCollectionFactory,
            'dataHelper' => $this->dataHelper,
        ];

        $this->dataHelper = $this->getMockBuilder(DataHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['displayGirthValue'])
            ->getMock();

        $this->carrier = $this->objectManager->getObject(Carrier::class, $arguments);
    }

    /**
     * @dataProvider codeDataProvider
     */
    public function testGetCodeArray($code)
    {
        $this->assertNotEmpty($this->carrier->getCode($code));
    }

    public function testGetCodeBool()
    {
        $this->assertFalse($this->carrier->getCode('test_code'));
    }

    public function testReturnOfShipment()
    {
        $this->httpResponse->method('getBody')
            ->willReturn(file_get_contents(__DIR__ . '/_files/success_usps_response_return_shipment.xml'));
        $request = $this->objectManager->getObject(
            ReturnShipment::class,
            require __DIR__ . '/_files/return_shipment_request_data.php'
        );
        $this->httpClient->expects(self::exactly(2))
            ->method('setParameterGet')
            ->withConsecutive(
                ['API', 'SignatureConfirmationCertifyV3'],
                ['XML', $this->stringContains('<WeightInOunces>80</WeightInOunces>')]
            );

        $this->assertNotEmpty($this->carrier->returnOfShipment($request)->getInfo()[0]['tracking_number']);
    }

    public function testFormattingFloatValuesForIntlShipmentRequest()
    {
        $this->httpResponse->method('getBody')
            ->willReturn(
                file_get_contents(__DIR__ . '/_files/success_usps_response_return_shipment.xml')
            );
        $request = $this->objectManager->getObject(
            ReturnShipment::class,
            require __DIR__ . '/_files/return_shipment_request_data.php'
        );

        $request->setRecipientAddressCountryCode('UK');
        $formattedValuesRegex = '(<Value>5.00<\/Value>).*';
        $formattedValuesRegex .= '(<NetOunces>0.00<\/NetOunces>)';

        $this->httpClient->expects($this->exactly(2))
            ->method('setParameterGet')
            ->withConsecutive(
                ['API', 'ExpressMailIntl'],
                ['XML', $this->matchesRegularExpression('/' . $formattedValuesRegex . '/')]
            );

        $this->carrier->returnOfShipment($request);
    }

    /**
     * Emulates the config's `getValue` method.
     *
     * @param $path
     * @return string|string
     */
    public function scopeConfigGetValue($path)
    {
        $pathMap = [
            'carriers/usps/allowed_methods' => '0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,' .
                '34,35,36,37,42,43,53,55,56,57,61,INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,' .
                'INT_12,INT_13,INT_14,INT_15,INT_16,INT_20,INT_26',
            'carriers/usps/showmethod' => 1,
            'carriers/usps/debug' => 1,
            'carriers/usps/userid' => 'test',
            'carriers/usps/mode' => 0,
        ];

        return $pathMap[$path] ?? null;
    }

    /**
     * @return array
     */
    public function codeDataProvider()
    {
        return [['container'], ['machinable'], ['method'], ['size']];
    }

    public function testCollectRatesErrorMessage()
    {
        $this->scope->method('isSetFlag')
            ->willReturn(false);

        $this->error->method('setCarrier')
            ->with('usps');
        $this->error->expects($this->once())
            ->method('setCarrierTitle');
        $this->error->expects($this->once())
            ->method('setErrorMessage');

        $request = new RateRequest();
        $this->assertSame($this->error, $this->carrier->collectRates($request));
    }

    /**
     * @param string $data
     * @param array $maskFields
     * @param string $expected
     * @dataProvider logDataProvider
     */
    public function testFilterDebugData($data, array $maskFields, $expected)
    {
        $refClass = new \ReflectionClass(Carrier::class);
        $property = $refClass->getProperty('_debugReplacePrivateDataKeys');
        $property->setAccessible(true);
        $property->setValue($this->carrier, $maskFields);

        $refMethod = $refClass->getMethod('filterDebugData');
        $refMethod->setAccessible(true);
        $result = $refMethod->invoke($this->carrier, $data);
        $expectedXml = new \SimpleXMLElement($expected);
        $resultXml = new \SimpleXMLElement($result);
        $this->assertEquals($expectedXml->asXML(), $resultXml->asXML());
    }

    /**
     * Get list of variations
     */
    public function logDataProvider()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
                <RateRequest USERID="12312">
                    <Package ID="0">
                        <Service>ALL</Service>
                    </Package>
                </RateRequest>',
                ['USERID'],
                '<?xml version="1.0" encoding="UTF-8"?>
                <RateRequest USERID="****">
                    <Package ID="0">
                        <Service>ALL</Service>
                    </Package>
                </RateRequest>',
            ],
        ];
    }

    /**
     * @param string $countyCode
     * @param string $carrierMethodCode
     * @param bool $displayGirthValueResult
     * @param bool $result
     * @dataProvider isGirthAllowedDataProvider
     */
    public function testIsGirthAllowed($countyCode, $carrierMethodCode, $displayGirthValueResult, $result)
    {
        $this->dataHelper->method('displayGirthValue')
            ->with($carrierMethodCode)
            ->willReturn($displayGirthValueResult);

        $this->assertEquals($result, $this->carrier->isGirthAllowed($countyCode, $carrierMethodCode));
    }

    /**
     * @return array
     */
    public function isGirthAllowedDataProvider()
    {
        return [
            ['US', 'usps_1', true, false],
            ['UK', 'usps_1', true, true],
            ['US', 'usps_0', false, true],
        ];
    }

    /**
     * @return MockObject
     */
    private function getXmlFactory(): MockObject
    {
        $xmlElFactory = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $xmlElFactory->method('create')
            ->willReturnCallback(
                function ($data) {
                    $helper = new ObjectManager($this);

                    return $helper->getObject(
                        Element::class,
                        ['data' => $data['data']]
                    );
                }
            );

        return $xmlElFactory;
    }

    /**
     * @return MockObject
     */
    private function getRateFactory(): MockObject
    {
        $rateFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $rateResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $rateFactory->method('create')
            ->willReturn($rateResult);

        return $rateFactory;
    }

    /**
     * @return MockObject
     */
    private function getRateMethodFactory(): MockObject
    {
        $rateMethodFactory = $this->getMockBuilder(MethodFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $rateMethod = $this->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPrice'])
            ->getMock();
        $rateMethod->method('setPrice')
            ->willReturnSelf();
        $rateMethodFactory->method('create')
            ->willReturn($rateMethod);

        return $rateMethodFactory;
    }

    /**
     * @return MockObject
     */
    private function getHttpClientFactory(): MockObject
    {
        $this->httpResponse = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBody'])
            ->getMock();
        $this->httpClient = $this->getMockBuilder(ZendClient::class)
            ->getMock();
        $this->httpClient->method('request')
            ->willReturn($this->httpResponse);
        $httpClientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')
            ->willReturn($this->httpClient);

        return $httpClientFactory;
    }

    /**
     * @return MockObject
     */
    private function getProductCollectionFactory(): MockObject
    {
        $productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->method('addStoreFilter')
            ->willReturnSelf();
        $productCollection->method('addFieldToFilter')
            ->willReturnSelf();
        $productCollection->method('addAttributeToSelect')
            ->willReturn([]);
        $productCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollectionFactory->method('create')
            ->willReturn($productCollection);

        return $productCollectionFactory;
    }

    /**
     * @return CarrierHelper
     */
    private function getCarrierHelper(): CarrierHelper
    {
        $localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $localeResolver->method('getLocale')->willReturn('fr_FR');
        $carrierHelper = $this->objectManager->getObject(
            CarrierHelper::class,
            [
                'localeResolver' => $localeResolver,
            ]
        );

        return $carrierHelper;
    }
}
