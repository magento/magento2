<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Paypal\Block\Payment\Info;
use Magento\Paypal\Model\Payflowlink;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayflowlinkTest extends \PHPUnit\Framework\TestCase
{
    /** @var Payflowlink */
    protected $model;

    /** @var  \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject */
    protected $infoInstance;

    /** @var  \Magento\Paypal\Model\Payflow\Request|\PHPUnit_Framework_MockObject_MockObject */
    protected $payflowRequest;

    /** @var  \Magento\Paypal\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $paypalConfig;

    /** @var  \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $store;

    /** @var  \Magento\Paypal\Model\Payflow\Service\Gateway|\PHPUnit_Framework_MockObject_MockObject */
    private $gatewayMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->store = $this->createMock(\Magento\Store\Model\Store::class);
        $storeManager = $this->createMock(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $this->paypalConfig = $this->getMockBuilder(\Magento\Paypal\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configFactoryMock = $this->getMockBuilder(\Magento\Payment\Model\Method\ConfigInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestFactory = $this->getMockBuilder(\Magento\Paypal\Model\Payflow\RequestFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payflowRequest = $this->getMockBuilder(\Magento\Paypal\Model\Payflow\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->infoInstance = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->gatewayMock = $this->getMockBuilder(\Magento\Paypal\Model\Payflow\Service\Gateway::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $configFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->paypalConfig);
        $this->payflowRequest->expects($this->any())
            ->method('__call')
            ->will($this->returnCallback(function ($method) {
                if (strpos($method, 'set') === 0) {
                    return $this->payflowRequest;
                }
                return null;
            }));
        $requestFactory->expects($this->any())->method('create')->will($this->returnValue($this->payflowRequest));

        $helper = new ObjectManagerHelper($this);
        $this->model = $helper->getObject(
            \Magento\Paypal\Model\Payflowlink::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $storeManager,
                'configFactory' => $configFactoryMock,
                'requestFactory' => $requestFactory,
                'gateway' => $this->gatewayMock,
            ]
        );
        $this->model->setInfoInstance($this->infoInstance);
    }

    public function testInitialize()
    {
        $storeId = 1;
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
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

        $response = new \Magento\Framework\DataObject(
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

        $this->payflowRequest->expects($this->exactly(3))
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
                    $this->returnSelf()
                ],
                ['USER1', 1, $this->returnSelf()],
                ['USER2', 'a20d3dc6824c1f7780c5529dc37ae5e', $this->returnSelf()]
            );

        $stateObject = new \Magento\Framework\DataObject();
        $this->model->initialize(\Magento\Paypal\Model\Config::PAYMENT_ACTION_AUTH, $stateObject);
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
    public function dataProviderForTestIsActive()
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
