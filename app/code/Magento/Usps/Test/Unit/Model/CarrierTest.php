<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Test\Unit\Model;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Usps\Helper\Data as DataHelper;
use Magento\Usps\Model\Carrier;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Zend_Http_Response|MockObject
     */
    private $httpResponse;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\Error|MockObject
     */
    private $error;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory|MockObject
     */
    private $errorFactory;

    /**
     * @var \Magento\Usps\Model\Carrier|MockObject
     */
    private $carrier;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|MockObject
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scope = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->scope->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnCallback([$this, 'scopeConfiggetValue'])
        );

        // xml element factory
        $xmlElFactory = $this->getMockBuilder(
            \Magento\Shipping\Model\Simplexml\ElementFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $xmlElFactory->expects($this->any())->method('create')->will(
            $this->returnCallback(
                function ($data) {
                    $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
                    return $helper->getObject(
                        \Magento\Shipping\Model\Simplexml\Element::class,
                        ['data' => $data['data']]
                    );
                }
            )
        );

        // rate factory
        $rateFactory = $this->getMockBuilder(
            \Magento\Shipping\Model\Rate\ResultFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $rateResult = $this->getMockBuilder(
            \Magento\Shipping\Model\Rate\Result::class
        )->disableOriginalConstructor()->setMethods(
            null
        )->getMock();
        $rateFactory->expects($this->any())->method('create')->will($this->returnValue($rateResult));

        // rate method factory
        $rateMethodFactory = $this->getMockBuilder(
            \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $rateMethod = $this->getMockBuilder(
            \Magento\Quote\Model\Quote\Address\RateResult\Method::class
        )->disableOriginalConstructor()->setMethods(
            ['setPrice']
        )->getMock();
        $rateMethod->expects($this->any())->method('setPrice')->will($this->returnSelf());

        $rateMethodFactory->expects($this->any())->method('create')->will($this->returnValue($rateMethod));

        // http client
        $this->httpResponse = $this->getMockBuilder(
            \Zend_Http_Response::class
        )->disableOriginalConstructor()->setMethods(
            ['getBody']
        )->getMock();

        $this->httpClient = $this->getMockBuilder(ZendClient::class)
            ->getMock();
        $this->httpClient->method('request')
            ->willReturn($this->httpResponse);

        $httpClientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')
            ->willReturn($this->httpClient);

        $data = ['id' => 'usps', 'store' => '1'];

        $this->error = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\Error::class)
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();

        $this->errorFactory = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->errorFactory->expects($this->any())->method('create')->willReturn($this->error);

        $arguments = [
            'scopeConfig' => $this->scope,
            'xmlSecurity' => new \Magento\Framework\Xml\Security(),
            'xmlElFactory' => $xmlElFactory,
            'rateFactory' => $rateFactory,
            'rateMethodFactory' => $rateMethodFactory,
            'httpClientFactory' => $httpClientFactory,
            'data' => $data,
            'rateErrorFactory' => $this->errorFactory,

        ];

        $this->dataHelper = $this->getMockBuilder(DataHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['displayGirthValue'])
            ->getMock();

        $this->carrier = $this->objectManager->getObject(\Magento\Usps\Model\Carrier::class, $arguments);

        $this->objectManager->setBackwardCompatibleProperty($this->carrier, 'dataHelper', $this->dataHelper);
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

    public function testCollectRates()
    {
        $expectedRequest = '<?xml version="1.0" encoding="UTF-8"?><RateV4Request USERID="213MAGEN6752">'
            . '<Revision>2</Revision><Package ID="0"><Service>ALL</Service><ZipOrigination/>'
            . '<ZipDestination>90032</ZipDestination><Pounds>4</Pounds><Ounces>4.2512000000</Ounces>'
            . '<Container>VARIABLE</Container><Size>REGULAR</Size><Machinable/></Package></RateV4Request>';
        $expectedXml = new \SimpleXMLElement($expectedRequest);

        $this->scope->method('isSetFlag')
            ->willReturn(true);

        $this->httpClient->expects(self::exactly(2))
            ->method('setParameterGet')
            ->withConsecutive(
                ['API', 'RateV4'],
                ['XML', self::equalTo($expectedXml->asXML())]
            );

        $this->httpResponse->method('getBody')
            ->willReturn(file_get_contents(__DIR__ . '/_files/success_usps_response_rates.xml'));

        $data = require __DIR__ . '/_files/rates_request_data.php';
        $request = $this->objectManager->getObject(RateRequest::class, ['data' => $data[0]]);

        self::assertNotEmpty($this->carrier->collectRates($request)->getAllRates());
    }

    public function testCollectRatesWithUnavailableService()
    {
        $expectedCount = 5;

        $this->scope->expects(static::once())
            ->method('isSetFlag')
            ->willReturn(true);

        $this->httpResponse->expects(static::once())
            ->method('getBody')
            ->willReturn(file_get_contents(__DIR__ . '/_files/response_rates.xml'));

        $data = require __DIR__ . '/_files/rates_request_data.php';
        $request = $this->objectManager->getObject(RateRequest::class, ['data' => $data[1]]);
        $rates = $this->carrier->collectRates($request)->getAllRates();
        static::assertEquals($expectedCount, count($rates));
    }

    public function testReturnOfShipment()
    {
        $this->httpResponse->expects(
            $this->any()
        )->method(
            'getBody'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/success_usps_response_return_shipment.xml'))
        );
        $request = $this->objectManager->getObject(
            \Magento\Shipping\Model\Shipment\ReturnShipment::class,
            require __DIR__ . '/_files/return_shipment_request_data.php'
        );
        $this->assertNotEmpty($this->carrier->returnOfShipment($request)->getInfo()[0]['tracking_number']);
    }

    /**
     * Emulates the config's `getValue` method.
     *
     * @param $path
     * @return string|string
     */
    public function scopeConfigGetValue($path)
    {
        switch ($path) {
            case 'carriers/usps/allowed_methods':
                return '0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,34,35,36,37,42,43,53,' .
                    '55,56,57,61,INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,INT_12,INT_13,INT_14,' .
                    'INT_15,INT_16,INT_20,INT_26';
            case 'carriers/usps/showmethod':
                return 1;
            default:
                return null;
        }
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
        $this->scope->expects($this->once())->method('isSetFlag')->willReturn(false);

        $this->error->expects($this->once())->method('setCarrier')->with('usps');
        $this->error->expects($this->once())->method('setCarrierTitle');
        $this->error->expects($this->once())->method('setErrorMessage');

        $request = new RateRequest();
        $this->assertSame($this->error, $this->carrier->collectRates($request));
    }

    public function testCollectRatesFail()
    {
        $this->scope->expects($this->once())->method('isSetFlag')->willReturn(true);
        $this->scope->expects($this->atLeastOnce())->method('getValue')->willReturnMap(
            [
                ['carriers/usps/userid' => 123],
                ['carriers/usps/container' => 11],

            ]
        );
        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertNotEmpty($this->carrier->collectRates($request));
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
        static::assertEquals($expectedXml->asXML(), $resultXml->asXML());
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
        $this->dataHelper->expects(static::any())
            ->method('displayGirthValue')
            ->with($carrierMethodCode)
            ->willReturn($displayGirthValueResult);

        self::assertEquals($result, $this->carrier->isGirthAllowed($countyCode, $carrierMethodCode));
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
}
