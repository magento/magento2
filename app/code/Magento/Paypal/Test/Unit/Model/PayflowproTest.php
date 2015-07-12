<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Paypal\Model\Payflowpro
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Payflowpro;
use Magento\Store\Model\ScopeInterface;

/**
 * Class PayflowproTest
 */
class PayflowproTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payflowpro
     */
    protected $payflowpro;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\Payment\Model\Method\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Paypal\Model\Payflow\Service\Gateway|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gatewayMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $configFactoryMock = $this->getMock(
            'Magento\Payment\Model\Method\ConfigInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->configMock = $this->getMock(
            'Magento\Paypal\Model\PayflowConfig',
            [],
            [],
            '',
            false
        );
        $client = $this->getMock(
            'Magento\Framework\HTTP\ZendClient',
            [],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['getStore']
        );
        $this->gatewayMock = $this->getMock(
            'Magento\Paypal\Model\Payflow\Service\Gateway',
            [],
            [],
            '',
            false
        );
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();

        $configFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->configMock);

        $client->expects($this->any())->method('create')->will($this->returnSelf());
        $client->expects($this->any())->method('setUri')->will($this->returnSelf());
        $client->expects($this->any())->method('setConfig')->will($this->returnSelf());
        $client->expects($this->any())->method('setMethod')->will($this->returnSelf());
        $client->expects($this->any())->method('setParameterPost')->will($this->returnSelf());
        $client->expects($this->any())->method('setHeaders')->will($this->returnSelf());
        $client->expects($this->any())->method('setUrlEncodeBody')->will($this->returnSelf());
        $client->expects($this->any())->method('request')->will($this->returnSelf());
        $client->expects($this->any())->method('getBody')->will($this->returnValue('RESULT name=value&name2=value2'));

        $clientFactory = $this->getMock('Magento\Framework\HTTP\ZendClientFactory', ['create'], [], '', false);
        $clientFactory->expects($this->any())->method('create')->will($this->returnValue($client));

        $this->_helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->payflowpro = $this->_helper->getObject(
            'Magento\Paypal\Model\Payflowpro',
            [
                'configFactory' => $configFactoryMock,
                'httpClientFactory' => $clientFactory,
                'storeManager' => $this->storeManagerMock,
                'gateway' => $this->gatewayMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * @param mixed $amountPaid
     * @param string $paymentType
     * @param bool $expected
     * @dataProvider canVoidDataProvider
     */
    public function testCanVoid($amountPaid, $paymentType, $expected)
    {
        $payment = $this->_helper->getObject($paymentType);
        $payment->setAmountPaid($amountPaid);
        $this->payflowpro->setInfoInstance($payment);
        $this->assertEquals($expected, $this->payflowpro->canVoid());
    }

    /**
     * @return array
     */
    public function canVoidDataProvider()
    {
        return [
            [0, 'Magento\Sales\Model\Order\Payment', true],
            [null, 'Magento\Sales\Model\Order\Payment', true]
        ];
    }

    public function testCanCapturePartial()
    {
        $this->assertTrue($this->payflowpro->canCapturePartial());
    }

    public function testCanRefundPartialPerInvoice()
    {
        $this->assertTrue($this->payflowpro->canRefundPartialPerInvoice());
    }

    /**
     * test for _buildBasicRequest (BDCODE)
     */
    public function testFetchTransactionInfoForBN()
    {
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['getId'],
            [],
            '',
            false
        );
        $response = new \Magento\Framework\Object(
            [
                'result' => '0',
                'pnref' => 'V19A3D27B61E',
                'respmsg' => 'Approved',
                'authcode' => '510PNI',
                'hostcode' => 'A',
                'request_id' => 'f930d3dc6824c1f7230c5529dc37ae5e',
                'result_code' => '0',
            ]
        );

        $this->gatewayMock->expects($this->once())
            ->method('postRequest')
            ->willReturn($response);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(77);
        $this->configMock->expects($this->once())->method('getBuildNotationCode')
            ->will($this->returnValue('BNCODE'));
        $payment = $this->getMock('Magento\Payment\Model\Info', ['setTransactionId', '__wakeup'], [], '', false);
        $payment->expects($this->once())->method('setTransactionId')->will($this->returnSelf());
        $this->payflowpro->fetchTransactionInfo($payment, 'AD49G8N825');
    }


    /**
     * @param $response
     * @dataProvider setTransStatusDataProvider
     */
    public function testSetTransStatus($response, $paymentExpected)
    {
        $payment = $this->_helper->getObject('Magento\Payment\Model\Info');
        $this->payflowpro->setTransStatus($payment, $response);
        $this->assertEquals($paymentExpected->getData(), $payment->getData());
    }

    public function setTransStatusDataProvider()
    {
        return [
           [
                'response' => new \Magento\Framework\Object(
                    [
                        'pnref' => 'V19A3D27B61E',
                        'result_code' => Payflowpro::RESPONSE_CODE_APPROVED,
                    ]
                ),
                'paymentExpected' => new \Magento\Framework\Object(
                    [
                        'transaction_id' => 'V19A3D27B61E',
                        'is_transaction_closed' => 0,
                    ]
                ),
            ],
            [
                'response' => new \Magento\Framework\Object(
                    [
                        'pnref' => 'V19A3D27B61E',
                        'result_code' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER
                    ]
                ),
                'paymentExpected' => new \Magento\Framework\Object(
                    [
                        'transaction_id' => 'V19A3D27B61E',
                        'is_transaction_closed' => 0,
                        'is_transaction_pending' => true,
                        'is_fraud_detected' => true,
                    ]
                ),
            ],
        ];
    }

    /**
     * @param array $expectsMethods
     * @param bool $result
     *
     * @dataProvider dataProviderForTestIsActive
     */
    public function testIsActive(array $expectsMethods, $result)
    {
        $storeId = 15;

        $i = 0;
        foreach ($expectsMethods as $method => $isActive) {
            $this->scopeConfigMock->expects($this->at($i++))
                ->method('getValue')
                ->with(
                    "payment/{$method}/active",
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )->willReturn($isActive);
        }

        $this->assertEquals($result, $this->payflowpro->isActive($storeId));
    }

    /**
     * @return array
     */
    public function dataProviderForTestIsActive()
    {
        return [
            [
                'expectsMethods' => [
                    Config::METHOD_PAYFLOWPRO => 0,
                    Config::METHOD_PAYMENT_PRO => 1,
                ],
                'result' => true,
            ],
            [
                'expectsMethods' => [
                    Config::METHOD_PAYFLOWPRO => 1
                ],
                'result' => true,
            ],
            [
                'expectsMethods' => [
                    Config::METHOD_PAYFLOWPRO => 0,
                    Config::METHOD_PAYMENT_PRO => 0,
                ],
                'result' => false,
            ],
        ];
    }
}
