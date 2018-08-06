<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Model\Method\ConfigInterfaceFactory;
use Magento\Paypal\Block\Payment\Info;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Payflow\Request;
use Magento\Paypal\Model\Payflow\RequestFactory;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Paypal\Model\Payflowlink;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayflowlinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Payflowlink
     */
    private $model;

    /**
     * @var Payment|MockObject
     */
    private $infoInstance;

    /**
     * @var Request|MockObject
     */
    private $payflowRequest;

    /**
     * @var Config|MockObject
     */
    private $paypalConfig;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var Gateway|MockObject
     */
    private $gateway;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    protected function setUp()
    {
        $this->store = $this->createMock(Store::class);
        $storeManager = $this->createMock(
            StoreManagerInterface::class
        );
        $this->paypalConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configFactory = $this->getMockBuilder(ConfigInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestFactory = $this->getMockBuilder(RequestFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payflowRequest = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->infoInstance = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->gateway = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager->method('getStore')
            ->willReturn($this->store);
        $configFactory->method('create')
            ->willReturn($this->paypalConfig);
        $this->payflowRequest->method('__call')
            ->willReturnCallback(function ($method) {
                if (strpos($method, 'set') === 0) {
                    return $this->payflowRequest;
                }
                return null;
            });
        $requestFactory->method('create')
            ->willReturn($this->payflowRequest);

        $helper = new ObjectManagerHelper($this);
        $this->model = $helper->getObject(
            Payflowlink::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'storeManager' => $storeManager,
                'configFactory' => $configFactory,
                'requestFactory' => $requestFactory,
                'gateway' => $this->gateway,
            ]
        );
        $this->model->setInfoInstance($this->infoInstance);
    }

    public function testInitialize()
    {
        $storeId = 1;
        $order = $this->createMock(Order::class);
        $order->method('getStoreId')
            ->willReturn($storeId);
        $this->infoInstance->method('getOrder')
            ->willReturn($order);
        $this->infoInstance->method('setAdditionalInformation')
            ->willReturnSelf();
        $this->paypalConfig->method('getBuildNotationCode')
            ->willReturn('build notation code');

        $response = new DataObject(
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
        $this->gateway->method('postRequest')
            ->willReturn($response);

        $this->payflowRequest->expects(self::exactly(3))
            ->method('setData')
            ->willReturnMap(
                [
                    [
                        'user' => null,
                        'vendor' => null,
                        'partner' => null,
                        'pwd' => null,
                        'verbosity' => null,
                        'BUTTONSOURCE' => 'build notation code',
                        'tender' => 'C',
                    ],
                    self::returnSelf()
                ],
                ['USER1', 1, self::returnSelf()],
                ['USER2', 'a20d3dc6824c1f7780c5529dc37ae5e', self::returnSelf()]
            );

        $stateObject = new DataObject();
        $this->model->initialize(Config::PAYMENT_ACTION_AUTH, $stateObject);
        self::assertEquals($storeId, $this->model->getStore(), '{Store} should be set');
    }

    /**
     * @param bool $expectedResult
     * @param string $configResult
     * @dataProvider dataProviderForTestIsActive
     */
    public function testIsActive($expectedResult, $configResult)
    {
        $storeId = 15;
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                "payment/payflow_link/active",
                ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($configResult);

        $this->assertEquals($expectedResult, $this->model->isActive($storeId));
    }

    /**
     * @return array
     */
    public function dataProviderForTestIsActive()
    {
        return [
            [false, '0'],
            [true, '1']
        ];
    }

    public function testGetInfoBlockType()
    {
        static::assertEquals(Info::class, $this->model->getInfoBlockType());
    }
}
