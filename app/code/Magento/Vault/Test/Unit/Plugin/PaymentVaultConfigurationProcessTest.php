<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Plugin;

/**
 * Class PaymentVaultConfigurationProcessTest.
 */
class PaymentVaultConfigurationProcessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var \Magento\Vault\Api\PaymentMethodListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vaultList;

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Checkout\Block\Checkout\LayoutProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutProcessor;

    /**
     * @var \Magento\Vault\Plugin\PaymentVaultConfigurationProcess
     */
    private $plugin;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->storeManager = $this
            ->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->store = $this
            ->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->vaultList = $this
            ->getMockBuilder(\Magento\Vault\Api\PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getActiveList'])
            ->getMockForAbstractClass();
        $this->paymentMethodList = $this
            ->getMockBuilder(\Magento\Payment\Api\PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getActiveList'])
            ->getMockForAbstractClass();
        $this->layoutProcessor =  $this
            ->getMockBuilder(\Magento\Checkout\Block\Checkout\LayoutProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['process'])
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            \Magento\Vault\Plugin\PaymentVaultConfigurationProcess::class,
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

    /**
     * Data provider for BeforeProcess.
     *
     * @return array
     */
    public function beforeProcessDataProvider()
    {
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['renders']['children'] = [
            'vault' => [
                'methods' => []
            ],
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
            'vault' => [
                'methods' => []
            ],
            'braintree' => [
                'methods' => [
                    'braintree_paypal' => []
                ]
            ]
        ];

        $vaultPaymentMethod = $this
            ->getMockBuilder(\Magento\Vault\Api\PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getProviderCode'])
            ->getMockForAbstractClass();

        $vaultPaymentMethod->expects($this->any())->method('getCode')->willReturn('braintree_paypal_vault');
        $vaultPaymentMethod->expects($this->any())->method('getProviderCode')->willReturn('braintree_paypal');

        return [
            [$jsLayout, [], [], $result1],
            [$jsLayout, [$vaultPaymentMethod], [$vaultPaymentMethod], $result2]
        ];
    }
}
