<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    private $storeManager;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var PaymentMethodListInterface|MockObject
     */
    private $paymentMethodList;

    /**
     * @var LayoutProcessor|MockObject
     */
    private $layoutProcessor;

    /**
     * @var PaymentConfigurationProcess
     */
    private $plugin;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->storeManager = $this
            ->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->store = $this
            ->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->paymentMethodList = $this
            ->getMockBuilder(PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getActiveList'])
            ->getMockForAbstractClass();
        $this->layoutProcessor =  $this
            ->getMockBuilder(LayoutProcessor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['process'])
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            PaymentConfigurationProcess::class,
            [
                'paymentMethodList' => $this->paymentMethodList,
                'storeManager' => $this->storeManager
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
        if (!empty($activePaymentList)) {
            $activePaymentList[0] = $activePaymentList[0]($this);
            $activePaymentList[1] = $activePaymentList[1]($this);
        }
        $this->store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($this->store);
        $this->paymentMethodList->expects($this->once())
            ->method('getActiveList')
            ->with(1)
            ->willReturn($activePaymentList);

        $result = $this->plugin->beforeProcess($this->layoutProcessor, $jsLayout);
        $this->assertEquals($result[0], $expectedResult);
    }

    protected function getMockForPaymentMethod($return) {
        $payflowproPaymentMethod = $this
            ->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMockForAbstractClass();

        $payflowproPaymentMethod->expects(self::any())->method('getCode')->willReturn($return);

        return $payflowproPaymentMethod;
    }

    /**
     * Data provider for BeforeProcess.
     *
     * @return array
     */
    public static function beforeProcessDataProvider()
    {
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['renders']['children'] = [
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
            'paypal-payments' => [
                'methods' => [
                    'payflowpro' => [],
                    'payflow_link' => []
                ]
            ]
        ];

        $payflowproPaymentMethod = static fn (self $testCase) => $testCase->getMockForPaymentMethod('payflowpro');
        $payflowproLinkPaymentMethod = static fn (self $testCase) => $testCase->getMockForPaymentMethod('payflow_link');

        return [
            [$jsLayout, [], $result1],
            [$jsLayout, [$payflowproPaymentMethod, $payflowproLinkPaymentMethod], $result2]
        ];
    }
}
