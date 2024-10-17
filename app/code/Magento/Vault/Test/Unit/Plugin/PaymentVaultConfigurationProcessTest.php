<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\PaymentMethodListInterface;
use Magento\Vault\Plugin\PaymentVaultConfigurationProcess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentVaultConfigurationProcessTest extends TestCase
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
    private $vaultList;

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface|MockObject
     */
    private $paymentMethodList;

    /**
     * @var LayoutProcessor|MockObject
     */
    private $layoutProcessor;

    /**
     * @var PaymentVaultConfigurationProcess
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
        $this->vaultList = $this
            ->getMockBuilder(PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getActiveList'])
            ->getMockForAbstractClass();
        $this->paymentMethodList = $this
            ->getMockBuilder(\Magento\Payment\Api\PaymentMethodListInterface::class)
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
            PaymentVaultConfigurationProcess::class,
            [
                'vaultPaymentList' => $this->vaultList,
                'paymentMethodList' => $this->paymentMethodList,
                'storeManager' => $this->storeManager
            ]
        );
    }

    /**
     * @param array $jsLayout
     * @param array $activeVaultList
     * @param array $activePaymentList
     * @param array $expectedResult
     * @dataProvider beforeProcessDataProvider
     */
    public function testBeforeProcess($jsLayout, $activeVaultList, $activePaymentList, $expectedResult)
    {
        if (!empty($activeVaultList)) {
            $activeVaultList[0] = $activeVaultList[0]($this);
        }

        if (!empty($activePaymentList)) {
            $activePaymentList[0] = $activePaymentList[0]($this);
        }

        $this->store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($this->store);
        $this->vaultList->expects($this->once())->method('getActiveList')->with(1)->willReturn($activeVaultList);
        $this->paymentMethodList->expects($this->once())
            ->method('getActiveList')
            ->with(1)
            ->willReturn($activePaymentList);
        $result = $this->plugin->beforeProcess($this->layoutProcessor, $jsLayout);
        $this->assertEquals($result[0], $expectedResult);
    }

    protected function getMockForVaultPayment() {
        $vaultPaymentMethod = $this
            ->getMockBuilder(PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode', 'getProviderCode'])
            ->getMockForAbstractClass();

        $vaultPaymentMethod->expects($this->any())->method('getCode')->willReturn('payflowpro_cc_vault');
        $vaultPaymentMethod->expects($this->any())->method('getProviderCode')->willReturn('payflowpro');

        return $vaultPaymentMethod;
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
            'vault' => [
                'methods' => []
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
            'vault' => [
                'methods' => []
            ],
            'paypal-payments' => [
                'methods' => [
                    'payflowpro' => [],
                ]
            ]
        ];

        $vaultPaymentMethod = static fn (self $testCase) => $testCase->getMockForVaultPayment();

        return [
            [$jsLayout, [], [], $result1],
            [$jsLayout, [$vaultPaymentMethod], [$vaultPaymentMethod], $result2]
        ];
    }
}
