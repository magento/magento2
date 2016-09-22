<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model\Ui;

use Magento\Customer\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Vault\Model\Ui\TokensConfigProvider;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ConfigProviderTest
 *
 * @see \Magento\Vault\Model\Ui\TokensConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class TokensConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface|MockObject
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory|MockObject
     */
    private $paymentMethodInstanceFactory;

    /**
     * @var \Magento\Payment\Api\Data\PaymentMethodInterface|MockObject
     */
    private $vaultPayment;

    /**
     * @var VaultPaymentInterface|MockObject
     */
    private $vaultPaymentInstance;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var CustomerTokenManagement|MockObject
     */
    private $customerTokenManagement;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->paymentMethodList = $this->getMockBuilder(\Magento\Payment\Api\PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentMethodInstanceFactory = $this->getMockBuilder(
            \Magento\Payment\Model\Method\InstanceFactory::class
        )->disableOriginalConstructor()->getMock();

        $this->vaultPayment = $this->getMockForAbstractClass(\Magento\Payment\Api\Data\PaymentMethodInterface::class);
        $this->vaultPaymentInstance = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        $this->storeManager = $this->getMock(StoreManagerInterface::class);
        $this->store = $this->getMock(StoreInterface::class);

        $this->objectManager = new ObjectManager($this);
        $this->customerTokenManagement = $this->getMockBuilder(CustomerTokenManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetConfig()
    {
        $storeId = 1;
        $vaultProviderCode = 'vault_provider_code';

        $expectedConfig = [
            'payment' => [
                'vault' => [
                    $vaultProviderCode . '_item_' . '0' => [
                        'config' => ['token_code' => 'code'],
                        'component' => 'Vendor_Module/js/vault_component'
                    ]
                ]
            ]
        ];

        $token = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $tokenUiComponentProvider = $this->getMockForAbstractClass(TokenUiComponentProviderInterface::class);
        $tokenUiComponent = $this->getMockForAbstractClass(TokenUiComponentInterface::class);

        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);

        $this->paymentMethodList->expects(static::once())
            ->method('getActiveList')
            ->with($storeId)
            ->willReturn([$this->vaultPayment]);

        $this->paymentMethodInstanceFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->vaultPaymentInstance);
        
        $this->vaultPaymentInstance->expects(static::once())
            ->method('getProviderCode')
            ->willReturn($vaultProviderCode);

        $this->customerTokenManagement->expects(static::once())
            ->method('getCustomerSessionTokens')
            ->willReturn([$token]);
        
        $token->expects(static::once())
            ->method('getPaymentMethodCode')
            ->willReturn($vaultProviderCode);

        $tokenUiComponentProvider->expects(static::once())
            ->method('getComponentForToken')
            ->with($token)
            ->willReturn($tokenUiComponent);
        $tokenUiComponent->expects(static::once())
            ->method('getConfig')
            ->willReturn(['token_code' => 'code']);
        $tokenUiComponent->expects(static::once())
            ->method('getName')
            ->willReturn('Vendor_Module/js/vault_component');

        $configProvider = new TokensConfigProvider(
            $this->storeManager,
            $this->customerTokenManagement,
            [
                $vaultProviderCode => $tokenUiComponentProvider
            ]
        );

        $this->objectManager->setBackwardCompatibleProperty(
            $configProvider,
            'paymentMethodList',
            $this->paymentMethodList
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $configProvider,
            'paymentMethodInstanceFactory',
            $this->paymentMethodInstanceFactory
        );

        static::assertEquals($expectedConfig, $configProvider->getConfig());
    }
}
