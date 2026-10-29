<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Api\PaymentMethodListInterface as PaymentPaymentMethodListInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\PaymentMethodListInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Vault\Plugin\PaymentVaultConfigurationProcess;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentVaultConfigurationProcessTest extends TestCase
{
    use MockCreationTrait;

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
     * @var PaymentPaymentMethodListInterface|MockObject
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
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->store = $this->createMock(StoreInterface::class);
        $this->vaultList = $this->createMock(PaymentMethodListInterface::class);
        $this->paymentMethodList = $this->createMock(PaymentPaymentMethodListInterface::class);
        $this->layoutProcessor = $this->createMock(LayoutProcessor::class);

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
     */
    #[DataProvider('beforeProcessDataProvider')]
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

    protected function getMockForVaultPayment()
    {
        $vaultPaymentMethod = $this->createMock(VaultPaymentInterface::class);

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
