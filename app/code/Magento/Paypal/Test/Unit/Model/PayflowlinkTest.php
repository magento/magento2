<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayflowlinkTest extends TestCase
{
    /** @var Payflowlink */
    protected $model;

    /** @var  Payment|MockObject */
    protected $infoInstance;

    /** @var  Request|MockObject */
    protected $payflowRequest;

    /** @var  Config|MockObject */
    protected $paypalConfig;

    /** @var  Store|MockObject */
    protected $store;

    /** @var  Gateway|MockObject */
    private $gatewayMock;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->store = $this->createMock(Store::class);
        $storeManager = $this->createMock(
            StoreManagerInterface::class
        );
        $this->paypalConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configFactoryMock = $this->getMockBuilder(ConfigInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestFactory = $this->getMockBuilder(RequestFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payflowRequest = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->infoInstance = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->gatewayMock = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager->expects($this->any())->method('getStore')->willReturn($this->store);
        $configFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->paypalConfig);
        $this->payflowRequest->expects($this->any())
            ->method('__call')
            ->willReturnCallback(function ($method) {
                if (strpos($method, 'set') === 0) {
                    return $this->payflowRequest;
                }
                return null;
            });
        $requestFactory->expects($this->any())->method('create')->willReturn($this->payflowRequest);

        $helper = new ObjectManagerHelper($this);
        $helper->prepareObjectManager([]);
        $this->model = $helper->getObject(
            Payflowlink::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $storeManager,
                'configFactory' => $configFactoryMock,
                'requestFactory' => $requestFactory,
                'gateway' => $this->gatewayMock,
                'mathRandom' => new Random()
            ]
        );
        $this->model->setInfoInstance($this->infoInstance);
    }

    public function testInitialize()
    {
        $storeId = 1;
        $order = $this->createMock(Order::class);
        $order->expects($this->exactly(2))
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->infoInstance->expects($this->any())
            ->method('getOrder')
            ->willReturn($order);
        $this->infoInstance->expects($this->any())
            ->method('setAdditionalInformation')
            ->willReturnSelf();
        $this->paypalConfig->expects($this->once())
            ->method('getBuildNotationCode')
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
        $this->gatewayMock->expects($this->once())
            ->method('postRequest')
            ->willReturn($response);

        $this->payflowRequest->expects($this->exactly(4))
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
                    ]
                ]
            )->willReturnSelf();

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
        $this->scopeConfigMock->expects($this->once())
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
    public static function dataProviderForTestIsActive()
    {
        return [
            [false, '0'],
            [true, '1']
        ];
    }

    /**
     * @covers \Magento\Paypal\Model\Payflowlink::getInfoBlockType()
     */
    public function testGetInfoBlockType()
    {
        static::assertEquals(Info::class, $this->model->getInfoBlockType());
    }
}
