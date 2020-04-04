<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Plugin\PaymentConfigurationProcess;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentConfigurationProcessTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var PaymentMethodListInterface|MockObject
     */
    private $paymentMethodListMock;

    /**
     * @var LayoutProcessor|MockObject
     */
    private $layoutProcessorMock;

    /**
     * @var PaymentConfigurationProcess
     */
    private $plugin;

    protected function setUp()
    {
        $this->storeManagerMock = $this
            ->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->storeMock = $this
            ->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->paymentMethodListMock = $this
            ->getMockBuilder(PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getActiveList'])
            ->getMockForAbstractClass();
        $this->layoutProcessorMock = $this
            ->getMockBuilder(LayoutProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['process'])
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            PaymentConfigurationProcess::class,
            [
                'paymentMethodList' => $this->paymentMethodListMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * @param array $jsLayout
     * @param array $activePaymentList
     * @param array $expectedResult
     * @dataProvider beforeProcessDataProvider
     */
    public function testBeforeProcess($jsLayout, $activePaymentList, $expectedResult)
    {
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->paymentMethodListMock->expects($this->once())
            ->method('getActiveList')
            ->with(1)
            ->willReturn($activePaymentList);

        $result = $this->plugin->beforeProcess($this->layoutProcessorMock, $jsLayout);
        $this->assertEquals($result[0], $expectedResult);
    }

    /**
     * Data provider for BeforeProcess.
     *
     * @return array
     */
    public function beforeProcessDataProvider()
    {
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['renders']['children'] = [
            'braintree' => [
                'methods' => [
                    'braintree_paypal' => [],
                    'braintree' => []
                ]
            ],
            'paypal-payments' => [
                'methods' => [
                    'payflowpro' => [],
                    'payflow_link' => []
                ]
            ]
        ];
        $result1['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['renders']['children'] = [];
        $result2['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['renders']['children'] = [
            'braintree' => [
                'methods' => [
                    'braintree' => [],
                    'braintree_paypal' => []
                ]
            ]
        ];

        $braintreePaymentMethod = $this
            ->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMockForAbstractClass();
        $braintreePaypalPaymentMethod = $this
            ->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMockForAbstractClass();

        $braintreePaymentMethod->expects($this->any())->method('getCode')->willReturn('braintree');
        $braintreePaypalPaymentMethod->expects($this->any())->method('getCode')->willReturn('braintree_paypal');

        return [
            [$jsLayout, [], $result1],
            [$jsLayout, [$braintreePaymentMethod, $braintreePaypalPaymentMethod], $result2]
        ];
    }
}
